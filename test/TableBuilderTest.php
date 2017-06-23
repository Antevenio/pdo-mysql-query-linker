<?php
use PdoMysqlQueryLinker\TableBuilder;
use PHPUnit\Framework\TestCase;

class TableBuilderTest extends TestCase
{
    /**
     * @var TableBuilder
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new TableBuilder(
            new TableBuilder\Types(),
            new TableBuilder\Flags()
        );
    }

    public function testGetTableDefinition()
    {
        $metadata = [
            [
                "native_type" => "LONG",
                "flags" => [
                    "not_null",
                    "primary_key"
                ],
                "name" => "ID",
                "len" => 10,
                "precision" => 0
            ],
            [
                "native_type" => "VAR_STRING",
                "flags" => [],
                "name" => "STATUS",
                "len" => 60,
                "precision" => 0
            ],
            [
                "native_type" => "TINY",
                "flags" => [],
                "name" => "TINYTOONS",
                "len" => 1,
                "precision" => 0
            ],
            [
                "native_type" => "DOUBLE",
                "flags" => [],
                "name" => "DOUBLEX",
                "len" => 8,
                "precision" => 2
            ],
            [
                "native_type" => "DATETIME",
                "flags" => [],
                "name" => "DATEANDTIME",
                "len" => 8,
                "precision" => 2
            ]
        ];

        $expectedDefinition = "CREATE TABLE tmp (\n" .
            "\tID INT(10) NOT NULL, INDEX(ID),\n" .
            "\tSTATUS VARCHAR(60),\n" .
            "\tTINYTOONS TINYINT,\n" .
            "\tDOUBLEX DOUBLE(8,2),\n" .
            "\tDATEANDTIME DATETIME\n" .
            ")";

        $this->assertEquals(
            $expectedDefinition,
            $this->sut->getTableDefinition($metadata, "tmp")
        );
    }
}