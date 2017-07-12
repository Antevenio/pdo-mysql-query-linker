<?php
namespace PdoMysqlQueryLinker\Linker;

use PdoMysqlQueryLinker\Linker;
use PdoMysqlQueryLinker\TableBuilder;

class Factory {
    public function create()
    {
        return new Linker(
            (new TableBuilder\Factory())->create(),
            new \PdoMysqlSelectIterator\Factory()
        );
    }
}