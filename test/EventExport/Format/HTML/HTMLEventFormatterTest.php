<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\EventSpecificationInterface;
use CultuurNet\UDB3\EventExport\Format\HTML\Properties\TaalicoonDescription;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\UitpasEventInfo;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\UitpasEventInfoServiceInterface;
use ValueObjects\String\String;

class HTMLEventFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HTMLEventFormatter
     */
    protected $eventFormatter;

    public function setUp()
    {
        $this->eventFormatter = new HTMLEventFormatter();
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function getJSONEventFromFile($fileName)
    {
        $jsonEvent = file_get_contents(__DIR__ . '/../../samples/' . $fileName);
        return $jsonEvent;
    }

    /**
     * @param string $fileName
     * @return array
     */
    protected function getFormattedEventFromJSONFile($fileName)
    {
        $event = $this->getJSONEventFromFile($fileName);
        return $this->eventFormatter->formatEvent($event);
    }

    /**
     * @param array $expected
     * @param array $actual
     */
    protected function assertEventFormatting($expected, $actual)
    {
        $this->assertLessThanOrEqual(300, mb_strlen($actual['description']));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_distills_event_info_to_what_is_needed_for_html_export()
    {
        $freeEvent = $this->getFormattedEventFromJSONFile('event_with_terms.json');
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
            'brands' => array()
        ];
        $this->assertEventFormatting($expectedFormattedFreeEvent, $freeEvent);

        $pricedEvent = $this->getFormattedEventFromJSONFile('event_with_price.json');
        $expectedFormattedPricedEvent = $expectedFormattedFreeEvent;
        $expectedFormattedPricedEvent['price'] = '10,5';
        $this->assertEventFormatting($expectedFormattedPricedEvent, $pricedEvent);
    }

    /**
     * @test
     */
    public function it_indicates_if_price_is_unknown()
    {
        $eventWithoutBookingInfo = $this->getFormattedEventFromJSONFile('event_without_bookinginfo.json');
        $expectedFormattedEvent = [
            'image' => 'http://media.uitdatabank.be/20141211/558bb7cf-5ff8-40b4-872b-5f5b46bb16c2.jpg',
            'type' => 'Cursus of workshop',
            'title' => 'Lessenreeks MURGA',
            'description' => "Wij zijn Murga çava, een vrolijke groep van 20 percussionisten,\njong en oud, uit Herent en omgeving. Bij ons is iedereen welkom!\nMuzikale voorkennis is geen vereiste. Behalve percussie staan we\nook open voor blazers, dansers of ander talent. Kom gratis met ons\nkennismaken op maandag 1 september...",
            'address' => [
                'name' => 'GC De Wildeman',
                'street' => 'Schoolstraat 15',
                'postcode' => '3020',
                'municipality' => 'Herent',
            ],
            'price' => 'Niet ingevoerd',
            'dates' => "van 01/09/14 tot 29/06/15",
            'brands' => array()
        ];
        $this->assertEventFormatting($expectedFormattedEvent, $eventWithoutBookingInfo);
    }

    /**
     * @test
     */
    public function it_gracefully_handles_events_without_image()
    {
        $eventWithoutImage = $this->getFormattedEventFromJSONFile('event_without_image.json');
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
            'brands' => array()
        ];
        $this->assertEventFormatting($expectedFormattedEvent, $eventWithoutImage);
    }

    /**
     * @test
     */
    public function it_strips_html_and_truncates_the_description()
    {
        $eventWithHTMLDescription = $this->getFormattedEventFromJSONFile('event_with_html_description.json');
        $this->assertEquals(
            "Opnieuw twee dagen na elkaar en ook ditmaal brengen ze drie\n" .
            "artiestenmee die garant staan voor authenticiteit en originaliteit.\n" .
            "De eerste gastis niemand minder dan Stoomboot, die in het seizoen\n" .
            "2014 doorbrakmet zijn bejubelde debuutalbum. Verder is ooK fluitist\n" .
            "Stefan Bracavalopnieuw van de...",
            $eventWithHTMLDescription['description']
        );
    }

    /**
     * @test
     */
    public function it_optionally_enriches_events_with_uitpas_info()
    {
        $eventWithoutImage = $this->getJSONEventFromFile('event_without_image.json');

        /** @var UitpasEventInfoServiceInterface|\PHPUnit_Framework_MockObject_MockObject $uitpas */
        $uitpas = $this->getMock(UitpasEventInfoServiceInterface::class);

        $prices = [
            [
               'price' => '1.5',
               'label' => 'Kansentarief voor UiTPAS Regio Aalst',
            ],
            [
                'price' => '3.0',
                'label' => 'Kansentarief voor kaarthouders uit een andere regio',
            ],
            [
                'price' => '150.0',
                'label' => 'Kansentarief voor UiTPAS Regio Aalst',
            ],
            [
                'price' => '30',
                'label' => 'Kansentarief voor kaarthouders uit een andere regio',
            ],
        ];

        $expectedPrices = [
            [
                'price' => '1,5',
                'label' => 'Kansentarief voor UiTPAS Regio Aalst',
            ],
            [
                'price' => '3',
                'label' => 'Kansentarief voor kaarthouders uit een andere regio',
            ],
            [
                'price' => '150',
                'label' => 'Kansentarief voor UiTPAS Regio Aalst',
            ],
            [
                'price' => '30',
                'label' => 'Kansentarief voor kaarthouders uit een andere regio',
            ],
        ];

        $advantages = [];

        $eventInfo = new UitpasEventInfo($prices, $advantages);

        $uitpas->expects($this->once())
            ->method('getEventInfo')
            ->with('d1f0e71d-a9a8-4069-81fb-530134502c58')
            ->willReturn($eventInfo);

        $eventFormatter = new HTMLEventFormatter($uitpas);

        $formattedEvent = $eventFormatter->formatEvent($eventWithoutImage);

        $expectedFormattedEvent = [
            'uitpas' => [
                'prices' => $expectedPrices,
                'advantages' => [],
            ],
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
            'brands' => array(),
        ];

        $this->assertEquals(
            $expectedFormattedEvent,
            $formattedEvent
        );
    }

    /**
     * @test
     */
    public function it_correctly_sets_the_taalicoon_count_and_description()
    {
        $eventWithAllTaaliconen = $this->getJSONEventFromFile(
            'event_with_all_icon_labels.json'
        );

        $formattedEvent = $this->eventFormatter->formatEvent(
            $eventWithAllTaaliconen
        );

        $this->assertEquals(4, $formattedEvent['taalicoonCount']);
        $this->assertEquals(
            TaalicoonDescription::VIER_TAALICONEN(),
            $formattedEvent['taalicoonDescription']
        );
    }

    /**
     * @test
     */
    public function it_shows_a_brand_when_it_passes_a_spec()
    {
        $event = $this->getJSONEventFromFile(
            'event_with_all_icon_labels.json'
        );

        /** @var EventSpecificationInterface|\PHPUnit_Framework_MockObject_MockObject $brandSpec */
        $brandSpec = $this->getMock(EventSpecificationInterface::class);
        $brandSpec->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->eventFormatter->showBrand(new String('acme'), $brandSpec);
        $formattedEvent = $this->eventFormatter->formatEvent($event);
        $this->assertContains('acme', $formattedEvent['brands']);
    }

    /**
     * @test
     */
    public function it_adds_the_starting_age_when_event_has_age_range()
    {
        $event = $this->getJSONEventFromFile(
            'event_with_all_icon_labels.json'
        );

        $formattedEvent = $this->eventFormatter->formatEvent($event);
        $this->assertEquals(5, $formattedEvent['ageFrom']);
    }
}
