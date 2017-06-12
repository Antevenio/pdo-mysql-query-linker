<?php
namespace PdoMysqlQueryLinker\TableBuilder;

class Flags
{
    const FLAG_MAP = [
        "not_null" => "NOT NULL",
        "primary_key" => "KEY",
        "multiple_key" => "KEY"
    ];

    protected function getSqlForFlag($flag)
    {
        return self::FLAG_MAP[$flag] ?: "";
    }

    protected function getSqlForMetaFlags(array $flags)
    {
        return implode(" ", array_map(array($this, "getSqlForFlag"), $flags));
    }

    public function getSql($columnMeta)
    {
        return $this->getSqlForMetaFlags($columnMeta["flags"]);
    }
}