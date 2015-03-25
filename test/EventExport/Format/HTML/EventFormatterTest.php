<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

class EventFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventFormatter
     */
    protected $eventFormatter;

    public function setUp()
    {
        $this->eventFormatter = new EventFormatter();
    }

    private function getJSONEventFromFile($fileName)
    {
        $jsonEvent = file_get_contents(
            __DIR__ . '/../../samples/' . $fileName
        );

        return $jsonEvent;
    }


    /**
     * @test
     */
    public function it_distills_event_info_to_what_is_needed_for_html_export()
    {
        $event = $this->getJSONEventFromFile('event_with_terms.json');

        $expectedFormattedEvent = [
            'title' => 'Koran, kaliefen en kruistochten - De fundamenten van de islam',
            'image' => 'http://media.uitdatabank.be/20141211/558bb7cf-5ff8-40b4-872b-5f5b46bb16c2.jpg',
            'description' => 'De islam is niet meer weg te denken uit onze maatschappij. Aan de hand van boeiende anekdotes doet Urbain Vermeulen de ontstaansgeschiedenis van de godsdienst uit de doeken. Hij verklaart hoe de islam zich verhoudt tot de andere wereldgodsdiensten en legt de oorsprong van de fundamentalistische…',
            'dates' => 'ma 02/03/15 van 13:30 tot 16:30  ma 09/03/15 van 13:30 tot 16:30  ma 16/03/15 van 13:30 tot 16:30  ma 23/03/15 van 13:30 tot 16:30  ma 30/03/15 van 13:30 tot 16:30 ',
            'address' => [
                'name' => 'Cultuurcentrum De Kruisboog',
            ]
        ];

        $this->assertEquals(
            $expectedFormattedEvent,
            $this->eventFormatter->formatEvent($event)
        );
    }
}
