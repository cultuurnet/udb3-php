<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use Broadway\EventStore\Event;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\EventSpecificationInterface;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event\EventAdvantage;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo\EventInfo;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo\EventInfoServiceInterface;
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
            'calendarType' => 'multiple',
            'startDate' => new \DateTime('2015-03-02T13:30:00+01:00'),
            'endDate' => new \DateTime('2015-03-30T16:30:00+02:00'),
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
            'calendarType' => "periodic",
            'startDate' => new \DateTime('2014-09-01T00:00:00+02:00'),
            'endDate' => new \DateTime('2015-06-29T00:00:00+02:00'),
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
            'calendarType' => 'multiple',
            'startDate' => new \DateTime('2015-03-02T13:30:00+01:00'),
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
     * @dataProvider uitpasInfoProvider
     * @param array $priceData
     * @param array $advantagesData
     */
    public function it_optionally_enriches_events_with_uitpas_info($priceData, $advantagesData)
    {
        $eventWithoutImage = $this->getJSONEventFromFile('event_without_image.json');

        /** @var EventInfoServiceInterface|\PHPUnit_Framework_MockObject_MockObject $uitpas */
        $uitpas = $this->getMock(EventInfoServiceInterface::class);

        $prices = $priceData['original'];
        $expectedPrices = $priceData['formatted'];

        $advantages = $advantagesData['original'];
        $expectedAdvantages = $advantagesData['formatted'];

        $eventInfo = new EventInfo($prices, $advantages);

        $uitpas->expects($this->once())
            ->method('getEventInfo')
            ->with('d1f0e71d-a9a8-4069-81fb-530134502c58')
            ->willReturn($eventInfo);

        $eventFormatter = new EventFormatter($uitpas);

        $formattedEvent = $eventFormatter->formatEvent($eventWithoutImage);

        $expectedFormattedEvent = [
            'uitpas' => [
                'prices' => $expectedPrices,
                'advantages' => $expectedAdvantages,
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
            'calendarType' => 'multiple',
            'startDate' => new \DateTime('2015-03-02T13:30:00+01:00'),
            'brands' => array(),
        ];

        $this->assertEquals(
            $expectedFormattedEvent,
            $formattedEvent
        );
    }

    public function uitpasInfoProvider()
    {
        // Prices and their expected formatting, and advantages and their expected formatting.
        $data = [
            [
                [
                    'original' => [
                        [
                            'price' => '1.5',
                            'label' => 'Kansentarief voor UiTPAS Regio Aalst',
                        ],
                    ],
                    'formatted' => [
                        [
                            'price' => '1,5',
                            'label' => 'Kansentarief voor UiTPAS Regio Aalst',
                        ],
                    ],
                ],
                [
                    'original' => [
                        EventAdvantage::KANSENTARIEF(),
                    ],
                    'formatted' => [
                        'Korting voor kansentarief',
                    ],
                ],
            ],
            [
                [
                    'original' => [
                        [
                            'price' => '3.0',
                            'label' => 'Kansentarief voor kaarthouders uit een andere regio',
                        ],
                    ],
                    'formatted' => [
                        [
                            'price' => '3',
                            'label' => 'Kansentarief voor kaarthouders uit een andere regio',
                        ],
                    ],
                ],
                [
                    'original' => [
                        EventAdvantage::POINT_COLLECTING(),
                        EventAdvantage::KANSENTARIEF(),
                    ],
                    'formatted' => [
                        'Spaar punten',
                        'Korting voor kansentarief',
                    ],
                ],
            ],
            [
                [
                    'original' => [
                        [
                            'price' => '150.0',
                            'label' => 'Kansentarief voor UiTPAS Regio Aalst',
                        ],
                    ],
                    'formatted' => [
                        [
                            'price' => '150',
                            'label' => 'Kansentarief voor UiTPAS Regio Aalst',
                        ]
                    ],
                ],
                [
                    'original' => [
                        EventAdvantage::KANSENTARIEF(),
                        EventAdvantage::POINT_COLLECTING(),
                    ],
                    'formatted' => [
                        'Spaar punten',
                        'Korting voor kansentarief',
                    ],
                ],
            ],
            [
                [
                    'original' => [
                        [
                            'price' => '30',
                            'label' => 'Kansentarief voor kaarthouders uit een andere regio',
                        ],
                    ],
                    'formatted' => [
                        [
                            'price' => '30',
                            'label' => 'Kansentarief voor kaarthouders uit een andere regio',
                        ]
                    ],
                ],
                [
                    'original' => [],
                    'formatted' => [],
                ]
            ],
            [
                [
                    'original' => [],
                    'formatted' => [],
                ],
                [
                    'original' => [
                        EventAdvantage::POINT_COLLECTING(),
                    ],
                    'formatted' => [
                        'Spaar punten',
                    ],
                ]
            ]
        ];

        return $data;
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
    public function it_shows_activity_branding()
    {
        $event = $this->getJSONEventFromFile(
            'event_with_all_icon_labels.json'
        );

        $formattedEvent = $this->eventFormatter->formatEvent($event);
        $this->assertContains('uitpas', $formattedEvent['brands']);
        $this->assertContains('vlieg', $formattedEvent['brands']);
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
