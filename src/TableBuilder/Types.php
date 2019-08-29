<?php

namespace PdoMysqlQueryLinker\TableBuilder;

class Types
{
    static $types = [];

    public function __construct()
    {
        static::$types = [
            "TINY" => (new Type())->setName("TINYINT"),
            "LONG" => (new Type())->setName("INT")->setUseLength(),
            "DOUBLE" => (new Type())->setName("DOUBLE")->setUseLength()->setUsePrecision(),
            "DATETIME" => (new Type())->setName("DATETIME"),
            "TIMESTAMP" => (new Type())->setName("TIMESTAMP"),
            "VAR_STRING" => (new Type())->setName("VARCHAR")->setUseLength()
        ];
    }

    protected function getType($nativeType) {
        assert(isset(static::$types[$nativeType]),
            "Unknown PDO native type \"".$nativeType."\"");
        return static::$types[$nativeType];
    }

    public function getSql(ColumnMeta $columnMeta) {
        return $this->getType($columnMeta->getNativeType())->getSql($columnMeta);
    }
}
