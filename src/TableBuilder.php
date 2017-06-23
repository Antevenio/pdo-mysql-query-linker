<?php

namespace PdoMysqlQueryLinker;

use PdoMysqlQueryLinker\TableBuilder\ColumnMeta;
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

    /**
     * TableBuilder constructor.
     * @param Types $types
     * @param Flags $flags
     */
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
            $meta = new ColumnMeta($columnMeta);
            if (!$isFirst) {
                $query .= ",\n";
            }
            $isFirst = false;
            $query .= "\t" . $meta->getColumnName() . " " .
                $this->types->getSql($meta);
            $constraints = $this->flags->getConstraintsSql($meta);
            if ($constraints) {
                $query .= " " . $constraints;
            }
            $indexes = $this->flags->getIndexesSql($meta);
            if ($indexes) {
                $query .= ", " . $indexes;
            }
        }
        $query .= "\n)";

        return $query;
    }
}