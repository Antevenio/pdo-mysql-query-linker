<?php

namespace PdoMysqlQueryLinker;

use PdoMysqlQueryLinker\TableBuilder\Flags;
use PdoMysqlQueryLinker\TableBuilder\Types;

class TableBuilder
{
    /**
     * @var Types
     */
    protected $types;
    /**
     * @var Flags
     */
    protected $flags;

    public function __construct(
        Types $types,
        Flags $flags
    )
    {
        $this->types = $types;
        $this->flags = $flags;
    }

    public function getTableDefinition(array $columnsMetadata, $tableName)
    {
        $query = "CREATE TABLE $tableName (\n";
        $isFirst = true;
        foreach ($columnsMetadata as $columnMeta) {
            if (!$isFirst) {
                $query .= ",\n";
            }
            $isFirst = false;
            $query .= "\t" . $this->getColumnName($columnMeta) . " " .
                $this->types->getSql($columnMeta) . " " .
                $this->flags->getSql($columnMeta);
        }
        $query .= "\n)";

        return $query;
    }

    protected function getColumnName($columnMeta)
    {
        return $columnMeta["name"];
    }

    public function getColumnNames(array $columnsMetadata)
    {
        return array_map(array($this, "getColumnName"), $columnsMetadata);
    }
}