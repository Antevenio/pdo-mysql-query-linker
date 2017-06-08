<?php
namespace PdoMysqlQueryLinker;

class Linker {
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
     * @param \PDO $originPDO
     * @return Linker
     */
    public function setOriginPDO(\PDO $originPDO)
    {
        $this->originPDO = $originPDO;

        return $this;
    }

    /**
     * @param \PDO $destinationPDO
     * @return Linker
     */
    public function setDestinationPDO(\PDO $destinationPDO)
    {
        $this->destinationPDO = $destinationPDO;

        return $this;
    }

    /**
     * @param string $originQuery
     * @return Linker
     */
    public function setOriginQuery($originQuery)
    {
        $this->originQuery = $originQuery;

        return $this;
    }

    /**
     * @param string $destinationQuery
     * @return Linker
     */
    public function setDestinationQuery($destinationQuery)
    {
        $this->destinationQuery = $destinationQuery;

        return $this;
    }

    public function __construct()
    {
        $this->originPDO = null;
        $this->destinationPDO = null;
        $this->originQuery = null;
        $this->destinationQuery = null;
    }

    public function query()
    {
        $this->queryAssertions();
        $statement = $this->originPDO->query($this->originQuery);
        $this->getStatementMetainfo($statement);
    }

    protected function getStatementMetainfo(\PDOStatement $statement)
    {
        $meta = [];
        for ($i = 0; $i < $statement->columnCount(); $i++ ) {
            $meta[] = $statement->getColumnMeta($i);
        }
        return $meta;
    }

    protected function queryAssertions()
    {
        assert($this->originPDO != null, "No origin pdo connection set!");
        assert($this->destinationPDO != null, "No destination pdo connection set!");
        assert($this->originQuery != null, "No origin query set!");
        assert($this->destinationQuery != null, "No destination query set!");
    }
}