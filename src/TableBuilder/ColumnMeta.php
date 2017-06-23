<?php

namespace PdoMysqlQueryLinker\TableBuilder;

class ColumnMeta
{
    protected $columnMeta;

    public function __construct($columnMeta)
    {
        $this->columnMeta = $columnMeta;
    }

    public function getColumnName()
    {
        return $this->columnMeta["name"];
    }

    public function getNativeType()
    {
        return $this->columnMeta["native_type"];
    }

    public function getFlags()
    {
        return $this->columnMeta["flags"];
    }

    public function getLength()
    {
        return $this->columnMeta["len"];
    }

    public function getPrecision()
    {
        return $this->columnMeta["precision"];
    }
}