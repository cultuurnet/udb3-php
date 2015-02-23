<?php


namespace test\Event\FileWriter;

use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\EventExport\FileWriter\JSONLDEventFormatter;

class JSONLDEventFormatterTest extends \PHPUnit_Framework_TestCase
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
        $formatter = new JSONLDEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithTerms);
        $decodedEvent = json_decode($formattedEvent);

        $this->assertObjectNotHasAttribute('terms', $decodedEvent);
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
        $formatter = new JSONLDEventFormatter($includedProperties);
        $expectedIncludedTerms = [
            (object)array(
                "label"=> "Geschiedenis",
                "domain" => "theme",
                "id" => "1.11.0.0.0"
            ),
            (object)array(
                "label" => "Cursus of workshop",
                "domain" => "eventtype",
                "id" => "0.3.1.0.0"
            )
        ];

        $formattedEvent = $formatter->formatEvent($eventWithTerms);
        $decodedEvent = json_decode($formattedEvent);

        $this->assertObjectHasAttribute('terms', $decodedEvent);

        $this->assertEquals($expectedIncludedTerms, $decodedEvent->terms);
    }
}
