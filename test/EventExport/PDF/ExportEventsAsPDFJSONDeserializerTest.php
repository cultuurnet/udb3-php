<?php

namespace CultuurNet\UDB3\EventExport\Command;

use ValueObjects\String\String;
use CultuurNet\Deserializer\MissingValueException;

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
        $exportData = $this->getJSONStringFromFile('export_data.json');
        $command = $this->deserializer->deserialize($exportData);

        $this->assertInstanceOf(ExportEventsAsPDF::class, $command);

    }

    /**
     * @test
     */
    public function it_expects_a_query_property()
    {
        $exportData = $this->getJSONStringFromFile('export_data_without_query.json');

        $this->setExpectedException(MissingValueException::class, 'query is missing');
        $this->deserializer->deserialize($exportData);
    }

    /**
     * @test
     */
    public function it_expects_a_brand_property()
    {
        $exportData = $this->getJSONStringFromFile('export_data_without_brand.json');

        $this->setExpectedException(MissingValueException::class, 'brand is missing');
        $this->deserializer->deserialize($exportData);
    }

    /**
     * @test
     */
    public function it_expects_a_title_property()
    {
        $exportData = $this->getJSONStringFromFile('export_data_without_title.json');

        $this->setExpectedException(MissingValueException::class, 'title is missing');
        $this->deserializer->deserialize($exportData);
    }

    private function getJSONStringFromFile($fileName)
    {
        $json = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        return new String($json);
    }
}
