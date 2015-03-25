<?php

namespace CultuurNet\UDB3\EventExport\Command;

use ValueObjects\String\String;

class ExportEventsAsPDFJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ExportEventsAsPDFJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new ExportEventsAsPDFJSONDeserializer();
    }

    /**
     * @test
     */
    public function it_returns_a_PDF_export_command()
    {
        $PDFExportData = new String($this->getFileContent('pdf_export_data.json'));
        $command = $this->deserializer->deserialize($PDFExportData);

        $this->assertInstanceOf(ExportEventsAsPDF::class, $command);

    }

    private function getFileContent($fileName)
    {
        $json = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        return $json;
    }
}
