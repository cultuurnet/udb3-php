<?php


namespace test\Event\FileWriter;

use CultuurNet\UDB3\EventExport\FileWriter\TabularDataEventFormatter;
use CultuurNet\UDB3\EventExport\FileWriter\TabularDataFileWriter;

class TabularDataEventFormatterTest extends \PHPUnit_Framework_TestCase
{

    private function getJSONEventFromFile($fileName)
    {
        $jsonEvent = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        return $jsonEvent;
    }

    /**
     * @test
     */
    public function it_excludes_all_terms_when_none_are_included()
    {
        $includedProperties = [
            'id',
            'name',
            'calendarSummary',
            'image',
            'location',
            'address',
            'bookingInfo'
        ];
        $eventWithTerms = $this->getJSONEventFromFile('event_with_terms.json');
        $formatter = new TabularDataEventFormatter(
            TabularDataFileWriter::columns(),
            $includedProperties
        );

        $formattedEvent = $formatter->formatEvent($eventWithTerms);

        $this->assertArrayNotHasKey('terms.theme', $formattedEvent);
        $this->assertArrayNotHasKey('terms.eventtype', $formattedEvent);
    }

    /**
     * @test
     */
    public function it_only_adds_and_formats_included_terms()
    {
        $includedProperties = [
            'id',
            'name',
            'calendarSummary',
            'image',
            'location',
            'address',
            'bookingInfo',
            'terms.eventtype',
            'terms.theme'
        ];
        $eventWithTerms = $this->getJSONEventFromFile('event_with_terms.json');
        $formatter = new TabularDataEventFormatter(
            TabularDataFileWriter::columns(),
            $includedProperties
        );
        $formattedEvent = $formatter->formatEvent($eventWithTerms);

        $this->assertArrayHasKey('terms.theme', $formattedEvent);
        $this->assertEquals(
            'Geschiedenis',
            $formattedEvent['terms.theme']
        );

        $this->assertArrayHasKey('terms.eventtype', $formattedEvent);
        $this->assertEquals(
            'Cursus of workshop',
            $formattedEvent['terms.eventtype']
        );
    }
}
