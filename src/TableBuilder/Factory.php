<?php
namespace PdoMysqlQueryLinker\TableBuilder;

use PdoMysqlQueryLinker\TableBuilder;

class Factory {
    public function create() {
        return new TableBuilder(new Types(), new Flags());
    }
}