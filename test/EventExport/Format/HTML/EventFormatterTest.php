<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\EventSpecificationInterface;
use ValueObjects\String\String;

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
        $freeEvent = $this->getJSONEventFromFile('event_with_terms.json');
        $expectedFormattedFreeEvent = [
            'title' => 'Koran, kaliefen en kruistochten - De fundamenten van de islam',
            'image' => 'http://media.uitdatabank.be/20141211/558bb7cf-5ff8-40b4-872b-5f5b46bb16c2.jpg',
            'description' => 'De islam is niet meer weg te denken uit onze maatschappij. Aan de hand van boeiende anekdotes doet Urbain Vermeulen de ontstaansgeschiedenis van de godsdienst uit de doeken. Hij verklaart hoe de islam zich verhoudt tot de andere wereldgodsdiensten en legt de oorsprong van de fundamentalistische...',
            'dates' => 'ma 02/03/15 van 13:30 tot 16:30  ma 09/03/15 van 13:30 tot 16:30  ma 16/03/15 van 13:30 tot 16:30  ma 23/03/15 van 13:30 tot 16:30  ma 30/03/15 van 13:30 tot 16:30 ',
            'address' => [
                'name' => 'Cultuurcentrum De Kruisboog',
                'street' => 'Sint-Jorisplein 20 ',
                'postcode' => '3300',
                'municipality' => 'Tienen',
            ],
            'type' => 'Cursus of workshop',
            'price' => 'Gratis',
            'taalicoonCount' => 0,
            'brands' => array()
        ];

        $this->assertEquals(
            $expectedFormattedFreeEvent,
            $this->eventFormatter->formatEvent($freeEvent)
        );

        $pricedEvent = $this->getJSONEventFromFile('event_with_price.json');
        $expectedFormattedPricedEvent = [
            'title' => 'Koran, kaliefen en kruistochten - De fundamenten van de islam',
            'image' => 'http://media.uitdatabank.be/20141211/558bb7cf-5ff8-40b4-872b-5f5b46bb16c2.jpg',
            'description' => 'De islam is niet meer weg te denken uit onze maatschappij. Aan de hand van boeiende anekdotes doet Urbain Vermeulen de ontstaansgeschiedenis van de godsdienst uit de doeken. Hij verklaart hoe de islam zich verhoudt tot de andere wereldgodsdiensten en legt de oorsprong van de fundamentalistische...',
            'dates' => 'ma 02/03/15 van 13:30 tot 16:30  ma 09/03/15 van 13:30 tot 16:30  ma 16/03/15 van 13:30 tot 16:30  ma 23/03/15 van 13:30 tot 16:30  ma 30/03/15 van 13:30 tot 16:30 ',
            'address' => [
                'name' => 'Cultuurcentrum De Kruisboog',
                'street' => 'Sint-Jorisplein 20 ',
                'postcode' => '3300',
                'municipality' => 'Tienen',
            ],
            'type' => 'Cursus of workshop',
            'price' => '10',
            'taalicoonCount' => 0,
            'brands' => array()
        ];

        $this->assertEquals(
            $expectedFormattedPricedEvent,
            $this->eventFormatter->formatEvent($pricedEvent)
        );
    }

    /**
     * @test
     */
    public function it_indicates_if_price_is_unknown()
    {
        $eventWithoutBookingInfo = $this->getJSONEventFromFile(
            'event_without_bookinginfo.json'
        );

        $expectedFormattedEvent = [
            'image' => 'http://media.uitdatabank.be/20141211/558bb7cf-5ff8-40b4-872b-5f5b46bb16c2.jpg',
            'type' => 'Cursus of workshop',
            'title' => 'Lessenreeks MURGA',
            'description' => 'Wij zijn Murga çava, een vrolijke groep van 20 percussionisten,...',
            'address' => [
                'name' => 'GC De Wildeman',
                'street' => 'Schoolstraat 15',
                'postcode' => '3020',
                'municipality' => 'Herent',
            ],
            'price' => 'Niet ingevoerd',
            'dates' => "van 01/09/14 tot 29/06/15",
            'taalicoonCount' => 0,
            'brands' => array()
        ];

        $this->assertEquals(
            $expectedFormattedEvent,
            $this->eventFormatter->formatEvent($eventWithoutBookingInfo)
        );
    }

    /**
     * @test
     */
    public function it_gracefully_handles_events_without_image()
    {
        $eventWithoutImage = $this->getJSONEventFromFile(
            'event_without_image.json'
        );

        $expectedFormattedEvent = [
            'type' => 'Cursus of workshop',
            'title' => 'Koran, kaliefen en kruistochten - De fundamenten van de islam',
            'description' => 'De islam is niet meer weg te denken uit onze maatschappij. Aan de hand van boeiende anekdotes doet Urbain Vermeulen de ontstaansgeschiedenis van de godsdienst uit de doeken. Hij verklaart hoe de islam zich verhoudt tot de andere wereldgodsdiensten en legt de oorsprong van de fundamentalistische...',
            'address' => [
                'name' => 'Cultuurcentrum De Kruisboog',
                'street' => 'Sint-Jorisplein 20 ',
                'postcode' => '3300',
                'municipality' => 'Tienen',
            ],
            'price' => 'Niet ingevoerd',
            'dates' => 'ma 02/03/15 van 13:30 tot 16:30  ma 09/03/15 van 13:30 tot 16:30  ma 16/03/15 van 13:30 tot 16:30  ma 23/03/15 van 13:30 tot 16:30  ma 30/03/15 van 13:30 tot 16:30 ',
            'taalicoonCount' => 0,
            'brands' => array()
        ];

        $this->assertEquals(
            $expectedFormattedEvent,
            $this->eventFormatter->formatEvent($eventWithoutImage)
        );
    }

    /**
     * @test
     */
    public function it_strips_html_and_truncates_the_description()
    {
        $eventWithHTMLDescription = $this->getJSONEventFromFile(
            'event_with_html_description.json'
        );

        $formattedEvent = $this->eventFormatter->formatEvent(
            $eventWithHTMLDescription
        );

        $this->assertEquals(
            'Opnieuw twee dagen na elkaar en ook ditmaal brengen ze drie...',
            $formattedEvent['description']
        );
    }

    /**
     * @test
     */
    public function it_correctly_sets_the_taalicoon_count()
    {
        $eventWithAllTaaliconen = $this->getJSONEventFromFile(
            'event_with_all_icon_labels.json'
        );

        $formattedEvent = $this->eventFormatter->formatEvent(
            $eventWithAllTaaliconen
        );

        $this->assertEquals(4, $formattedEvent['taalicoonCount']);
    }

    /**
     * @test
     */
    public function it_show_a_brand_when_it_passes_a_spec()
    {
        $event = $this->getJSONEventFromFile(
            'event_with_all_icon_labels.json'
        );

        /** @var EventSpecificationInterface $brandSpec */
        $brandSpec = $this->getMock(EventSpecificationInterface::class);
        $brandSpec->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->eventFormatter->showBrand(new String('acme'), $brandSpec);
        $formattedEvent = $this->eventFormatter->formatEvent($event);
        $this->assertContains('acme', $formattedEvent['brands']);
    }
}
