<?php

namespace CultuurNet\UDB3\EventExport\Command;

use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\UDB3\EventExport\EventExportQuery;
use CultuurNet\UDB3\EventExport\Format\HTML\Properties\Brand;
use CultuurNet\UDB3\EventExport\Format\HTML\Properties\Footer;
use CultuurNet\UDB3\EventExport\Format\HTML\Properties\Publisher;
use CultuurNet\UDB3\EventExport\Format\HTML\Properties\Subtitle;
use CultuurNet\UDB3\EventExport\Format\HTML\Properties\Title;
use ValueObjects\String\String;
use ValueObjects\Web\EmailAddress;

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
        $exportData = $this->getJSONStringFromFile('minimum_export_data.json');
        $command = $this->deserializer->deserialize($exportData);

        $this->assertInstanceOf(ExportEventsAsPDF::class, $command);

        $this->assertEquals(
            new ExportEventsAsPDF(
                new EventExportQuery('city:doetown'),
                new Brand('vlieg'),
                new Title('a title')
            ),
            $command
        );

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

    /**
     * @test
     * @dataProvider exportPropertyDataProvider
     */
    public function it_includes_optional_properties(
        $propertyName,
        $expectedValue,
        $getter
    ) {
        $exportData = $this->getJSONStringFromFile('export_data.json');
        $command = $this->deserializer->deserialize($exportData);

        $this->assertEquals($expectedValue, $command->{$getter}());

    }

    /**
     * Test property provider
     * property, value, getter
     */
    public function exportPropertyDataProvider()
    {
        return array(
            array('subtitle', new Subtitle('a subtitle'), 'getSubtitle'),
            array('publisher', new Publisher('a publisher'), 'getPublisher'),
            array('footer', new Footer('a footer'), 'getFooter'),
            array('email', new EmailAddress('john@doe.com'), 'getAddress'),
        );
    }

    private function getJSONStringFromFile($fileName)
    {
        $json = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        return new String($json);
    }
}
