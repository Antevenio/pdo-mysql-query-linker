<?php
namespace PdoMysqlQueryLinker\TableBuilder;

class Flags
{
    const CONSTRAINT_MAP = [
        "not_null" => "NOT NULL"
    ];

    const INDEX_MAP = [
        "primary_key" => "INDEX(?)",
        "multiple_key" => "INDEX(?)"
    ];

    protected function getConstraintSqlForFlag($flag)
    {
        return @self::CONSTRAINT_MAP[$flag] ?: "";
    }

    protected function getIndexSqlForFlag($flag)
    {
        return @self::INDEX_MAP[$flag] ?: "";
    }

    protected function getIndexSqlForFlags(array $flags)
    {
        return implode(",",
            array_filter(
                array_map([$this, "getIndexSqlForFlag"], $flags)
            )
        );
    }

    protected function getConstraintSqlForFlags(array $flags)
    {
        return implode(
            " ",
            array_filter(
                array_map(array($this, "getConstraintSqlForFlag"), $flags)
            )
        );
    }

    public function getConstraintsSql(ColumnMeta $columnMeta)
    {
        return $this->getConstraintSqlForFlags(
            $columnMeta->getFlags()
        );
    }

    public function getIndexesSql(ColumnMeta $columnMeta)
    {
        return preg_replace(
            "/\?/",
            $columnMeta->getColumnName(),
            $this->getIndexSqlForFlags($columnMeta->getFlags())
        );
    }
}