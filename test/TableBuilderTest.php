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

    public function testASample()
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
            ]
        ];

        $expectedDefinition = "CREATE TABLE tmp (\n" .
            "\tID INT(10) NOT NULL INDEX,\n" .
            "\tSTATUS VARCHAR(60) \n" .
            ")";

        $this->assertEquals(
            $expectedDefinition,
            $this->sut->getTableDefinition($metadata, "tmp")
        );
    }
}