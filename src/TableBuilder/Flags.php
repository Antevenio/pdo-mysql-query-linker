<?php
namespace PdoMysqlQueryLinker\TableBuilder;

class Flags
{
    const FLAG_MAP = [
        "not_null" => "NOT NULL",
        "primary_key" => "PRIMARY KEY",
        "multiple_key" => "INDEX"
    ];

    protected function getSqlForFlag($flag)
    {
        if (isset(self::FLAG_MAP[$flag])) {
            return self::FLAG_MAP[$flag];
        }
        return "";
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