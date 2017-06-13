<?php

namespace PdoMysqlQueryLinker;

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
    protected $destinationPDO;
    protected $originQuery;
    protected $destinationQuery;
    /**
     * @var TableBuilder
     */
    protected $tableBuilder;
    /**
     * @var Iterator\Factory
     */
    protected $iteratorFactory;
    protected $temporaryTable;
    protected $temporaryTablePopulated;

    /**
     * @param \PDO $originPDO
     * @return Linker
     */
    public function setOriginPDO(\PDO $originPDO)
    {
        $this->originPDO = $originPDO;
        $this->resetTemporaryTablePopulated();

        return $this;
    }

    /**
     * @param \PDO $destinationPDO
     * @return Linker
     */
    public function setDestinationPDO(\PDO $destinationPDO)
    {
        $this->destinationPDO = $destinationPDO;
        $this->resetTemporaryTablePopulated();

        return $this;
    }

    /**
     * @param string $originQuery
     * @return Linker
     */
    public function setOriginQuery($originQuery)
    {
        $this->originQuery = $originQuery;
        $this->resetTemporaryTablePopulated();

        return $this;
    }

    /**
     * @param string $destinationQuery
     * @return Linker
     */
    public function setDestinationQuery($destinationQuery)
    {
        $this->destinationQuery = $destinationQuery;
        $this->resetTemporaryTablePopulated();

        return $this;
    }

    public function __construct(
        TableBuilder $tableBuilder,
        Iterator\Factory $iteratorFactory
    )
    {
        $this->originPDO = null;
        $this->destinationPDO = null;
        $this->originQuery = null;
        $this->destinationQuery = null;

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
        $this->destinationPDO->query($tableDefinition);

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
            $this->temporaryTable, $this->destinationQuery
        );
    }

    public function execute()
    {
        $this->ensureTemporaryTablePopulated();

        return $this->destinationPDO->query($this->getQuery());
    }

    public function getIterator($blockSize)
    {
        $this->ensureTemporaryTablePopulated();

        return new Iterator($this->getQuery(), $blockSize);
    }

    public function destroyTemporaryTable()
    {
        $this->queryAssertions();
        $this->destinationPDO->query(
            "DROP TABLE IF EXISTS " . $this->temporaryTable
        );

        return $this;
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
        $ret = $this->destinationPDO->query($sql);
        if ($ret === false) {
            $exception = new \PDOException();
            $exception->errorInfo = $this->destinationPDO->errorInfo();
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
        assert($this->destinationPDO != null, "No destination pdo connection set!");
        assert($this->originQuery != null, "No origin query set!");
        assert($this->destinationQuery != null, "No destination query set!");
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