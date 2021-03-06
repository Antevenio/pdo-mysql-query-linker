<?php

namespace PdoMysqlQueryLinker;

use PdoMysqlSelectIterator\Factory;
use PdoMysqlSelectIterator\Iterator;

class Linker
{
    const TMP_TABLE_PLACEHOLDER = "{origin}";
    const TMP_PREFIX = "_tmpql_";
    const INTERNAL_ITERATOR_BLOCK_SIZE = 1000;
    /**
     * @var \PDO
     */
    protected $originPDO;
    /**
     * @var \PDO
     */
    protected $targetPDO;
    protected $originQuery;
    protected $targetQuery;
    /**
     * @var TableBuilder
     */
    protected $tableBuilder;
    /**
     * @var Factory
     */
    protected $iteratorFactory;
    protected $temporaryTable;
    protected $temporaryTablePopulated;

    /**
     * @param \PDO $originPDO
     * @param $originQuery
     * @return $this
     */
    public function origin(\PDO $originPDO, $originQuery)
    {
        $this->originPDO = $originPDO;
        $this->originQuery = $originQuery;
        $this->resetTemporaryTablePopulated();

        return $this;
    }

    /**
     * @param \PDO $targetPDO
     * @param $targetQuery
     * @return $this
     */
    public function target(\PDO $targetPDO, $targetQuery)
    {
        $this->targetPDO = $targetPDO;
        $this->targetQuery = $targetQuery;
        $this->resetTemporaryTablePopulated();

        return $this;
    }

    public function __construct(
        TableBuilder $tableBuilder,
        Factory $iteratorFactory
    )
    {
        $this->originPDO = null;
        $this->targetPDO = null;
        $this->originQuery = null;
        $this->targetQuery = null;

        $this->tableBuilder = $tableBuilder;
        $this->iteratorFactory = $iteratorFactory;

        $this->resetTemporaryTablePopulated();
    }

    protected function createTemporaryTable()
    {
        $this->queryAssertions();
        $this->temporaryTable = $this->getUniqueTablename();
        $statement = $this->originPDO->query($this->originQuery);
        $meta = $this->getStatementMetainfo($statement);
        $statement->closeCursor();
        $tableDefinition = $this->tableBuilder->getTableDefinition(
            $meta, $this->temporaryTable
        );
        $this->targetPDO->query($tableDefinition);

        return $this;
    }

    public function populateTemporaryTable()
    {
        $this->createTemporaryTable();
        $it = $this->iteratorFactory->create(
            $this->originPDO,
            $this->originQuery,
            self::INTERNAL_ITERATOR_BLOCK_SIZE
        );
        $tempFile = $this->createTemporaryCsv($it);
        $it->close();
        $this->loadDataLocalInfile($tempFile, $this->temporaryTable);
        $this->removeTemporaryFile($tempFile);
        $this->setTemporaryTablePopulated();

        return $this;
    }

    public function getQuery()
    {
        $this->ensureTemporaryTablePopulated();

        return preg_replace(
            "/" . preg_quote(self::TMP_TABLE_PLACEHOLDER) . "/",
            $this->temporaryTable, $this->targetQuery
        );
    }

    public function execute()
    {
        $this->ensureTemporaryTablePopulated();
        return $this->targetPDO->query($this->getQuery());
    }

    public function getIterator($blockSize)
    {
        $this->ensureTemporaryTablePopulated();

        return $this->iteratorFactory->create(
            $this->targetPDO, $this->getQuery(), $blockSize
        );
    }

    public function close()
    {
        $this->queryAssertions();
        $this->targetPDO->query(
            "DROP TABLE IF EXISTS " . $this->temporaryTable
        );
        $this->targetPDO = null;
        $this->originPDO = null;
    }

    protected function removeTemporaryFile($fileName)
    {
        unlink($fileName);
    }

    protected function loadDataLocalInfile(
        $file,
        $table
    ) {
        $sql = "LOAD DATA LOCAL INFILE '" . $file . "' IGNORE " .
            "INTO TABLE " . $table . " " .
            "CHARACTER SET utf8 " .
            "FIELDS TERMINATED BY ',' " .
            "OPTIONALLY ENCLOSED BY '\"' " .
            "ESCAPED BY '\\\\'";
        $ret = $this->targetPDO->query($sql);
        if ($ret === false) {
            $errorInfo = $this->targetPDO->errorInfo();
            $exception = new \PDOException(print_r($errorInfo, true));
            $exception->errorInfo = $errorInfo;
            throw $exception;
        }

        return $ret;
    }

    protected function getTemporaryCsvFilename() {
        return tempnam(sys_get_temp_dir(), self::TMP_PREFIX);
    }

    protected function createTemporaryCsv(Iterator $rowIterator)
    {
        $tmpFilepath = $this->getTemporaryCsvFilename();
        $f = fopen($tmpFilepath, "w");
        foreach ($rowIterator as $row) {
            fputcsv($f, $row);
        }
        fclose($f);

        return ($tmpFilepath);
    }

    protected function getStatementMetainfo(\PDOStatement $statement)
    {
        $meta = [];
        for ($i = 0; $i < $statement->columnCount(); $i++) {
            $meta[] = $statement->getColumnMeta($i);
        }

        return $meta;
    }

    protected function getUniqueTablename()
    {
        return uniqid(self::TMP_PREFIX, false);
    }

    protected function ensureTemporaryTablePopulated()
    {
        $this->queryAssertions();
        if (!$this->isTemporaryTablePopulated()) {
            $this->populateTemporaryTable();
        }
    }

    protected function queryAssertions()
    {
        assert($this->originPDO != null, "No origin pdo connection set!");
        assert($this->targetPDO != null, "No destination pdo connection set!");
        assert($this->originQuery != null, "No origin query set!");
        assert($this->targetQuery != null, "No destination query set!");
    }

    protected function isTemporaryTablePopulated()
    {
        return $this->temporaryTablePopulated;
    }

    protected function setTemporaryTablePopulated()
    {
        $this->temporaryTablePopulated = true;
    }

    protected function resetTemporaryTablePopulated()
    {
        $this->temporaryTablePopulated = false;
    }
}