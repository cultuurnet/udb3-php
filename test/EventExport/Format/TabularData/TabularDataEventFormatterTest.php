<?php


namespace CultuurNet\UDB3\EventExport\Format\TabularData;

use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event\EventAdvantage;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo\EventInfo;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo\EventInfoServiceInterface;

class TabularDataEventFormatterTest extends \PHPUnit_Framework_TestCase
{

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
    public function it_excludes_all_terms_when_none_are_included()
    {
        $includedProperties = [
            'id',
        ];
        $eventWithTerms = $this->getJSONEventFromFile('event_with_terms.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithTerms);
        $formattedProperties = array_keys($formattedEvent);

        $this->assertEquals($includedProperties, $formattedProperties);
    }

    /**
     * @test
     */
    public function it_excludes_other_terms_when_some_are_included()
    {
        $includedProperties = [
            'id',
            'terms.eventtype'
        ];
        $eventWithTerms = $this->getJSONEventFromFile('event_with_terms.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithTerms);
        $formattedProperties = array_keys($formattedEvent);

        $this->assertEquals($includedProperties, $formattedProperties);
    }

    /**
     * @test
     */
    public function it_formats_included_terms()
    {
        $includedProperties = [
            'id',
            'terms.eventtype',
            'terms.theme'
        ];
        $eventWithTerms = $this->getJSONEventFromFile('event_with_terms.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithTerms);
        $expectedFormatting = array(
            "id" =>"d1f0e71d-a9a8-4069-81fb-530134502c58",
            "terms.eventtype" => "Cursus of workshop",
            "terms.theme" => "Geschiedenis"
        );

        $this->assertEquals($expectedFormatting, $formattedEvent);
    }

    /**
     * @test
     */
    public function it_formats_address_as_separate_columns()
    {
        $includedProperties = [
            'id',
            'address'
        ];
        $eventWithTerms = $this->getJSONEventFromFile('event_with_terms.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithTerms);
        $expectedFormatting = array(
            "id" =>"d1f0e71d-a9a8-4069-81fb-530134502c58",
            "address.streetAddress" => "Sint-Jorisplein 20 ",
            "address.postalCode" => "3300",
            "address.addressLocality" => "Tienen",
            "address.addressCountry" => "BE"
        );

        $this->assertEquals($expectedFormatting, $formattedEvent);
    }

    /**
     * @test
     * @dataProvider eventDateProvider
     *
     * @param $eventFile
     * @param array $expectedFormattedEvent
     */
    public function it_formats_dates($eventFile, $expectedFormattedEvent)
    {
        $event = $this->getJSONEventFromFile($eventFile);

        $formatter = new TabularDataEventFormatter(
            array_keys($expectedFormattedEvent)
        );

        $formattedEvent = $formatter->formatEvent($event);

        // We do not care about the event 'id' here, which is always included.
        unset($formattedEvent['id']);

        $this->assertEquals($expectedFormattedEvent, $formattedEvent);
    }

    /**
     * Test data provider for it_formats_dates().
     *
     * @return array
     *   Array of individual arrays, each containing the arguments for the test method.
     */
    public function eventDateProvider()
    {
        return [
            [
                'event_with_dates.json',
                [
                    'created' => '2014-12-11 17:30',
                    'startDate' => '2015-03-02 13:30',
                    'endDate' => '2015-03-30 16:30',
                    'modified' => '',
                ],
            ],
            [
                'event_without_end_date.json',
                [
                    'created' => '2014-12-11 17:30',
                    'startDate' => '2015-03-02 13:30',
                    'endDate' => '',
                    'modified' => '',
                ],
            ],
            [
                'event_with_modified_date.json',
                [
                    'created' => '2015-10-13 14:27',
                    'startDate' => '2015-10-29 20:00',
                    'endDate' => '',
                    'modified' => '2015-10-13 14:27',
                ],
            ],
            [
                'event_with_outdated_start_and_end_date_format.json',
                [
                    'created' => '2014-12-11 17:30',
                    'startDate' => '2015-03-02 13:30',
                    'endDate' => '2015-03-30 16:30',
                    'modified' => '',
                ],
            ],
            [
                'event_with_incorrect_start_and_end_date_format.json',
                [
                    'created' => '2014-12-11 17:30',
                    'startDate' => '',
                    'endDate' => '',
                    'modified' => '',
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_can_format_an_empty_image()
    {
        $event = $this->getJSONEventFromFile('event_without_image.json');
        $formatter = new TabularDataEventFormatter(array('image'));
        $formattedEvent = $formatter->formatEvent($event);

        $this->assertTrue(isset($formattedEvent['image']));
        $this->assertEmpty($formattedEvent['image']);
    }

    /**
     * @test
     */
    public function it_can_format_event_with_a_contact_point()
    {
        $includedProperties = [
            'id',
            'contactPoint.email'
        ];
        $eventWithContactPoints = $this->getJSONEventFromFile('event_with_a_contact_point.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithContactPoints);
        $expectedFormatting = array(
            "id" =>"16744083-859a-4d3d-bd1d-16ea5bd3e2a3",
            "contactPoint.email" => "nicolas.leroy+test@gmail.com"
        );

        $this->assertEquals($expectedFormatting, $formattedEvent);
    }

    /**
     * @test
     */
    public function it_formats_available_date()
    {
        $includedProperties = [
            'id',
            'available'
        ];
        $eventWithAvailableDate = $this->getJSONEventFromFile('event_with_available_from.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithAvailableDate);
        $expectedFormatting = array(
            "id" =>"16744083-859a-4d3d-bd1d-16ea5bd3e2a3",
            "available" => "2015-10-13"
        );

        $this->assertEquals($expectedFormatting, $formattedEvent);
    }

    /**
     * @test
     */
    public function it_should_strip_line_breaking_white_spaces_that_are_not_set_by_markup()
    {
        $includedProperties = [
            'id',
            'description'
        ];
        $eventWithUnwantedLineBreaks = $this->getJSONEventFromFile('event_with_unwanted_line_breaks.json');

        $formatter = new TabularDataEventFormatter($includedProperties);
        $expectedDescription = 'Wat is de kracht van verzoening? Jan De Cock trekt de wereld rond en ontmoet tientallen slachtoffers van misdaden die we soms moeilijk kunnen vatten en die toch konden ze vergeven.'
        . PHP_EOL . 'Jan De Cock ontmoet slachtoffers van misdaden die het laatste woord niet aan de feiten hebben gelaten, noch aan de wrok.'
        . PHP_EOL . 'In een wereld waar de roep naar gerechtigheid steeds vaker gehoord wordt als een schreeuw voor meer repressie en straf, biedt Jan De Cock weerwerk.'
        . PHP_EOL . 'Hij trekt de wereld rond en ontmoet tientallen slachtoffers van daden die we soms moeilijk kunnen vatten.'
        . PHP_EOL . 'Toch konden ze vergeven: ouders van wie de kinderen door de Noor Breivik werden vermoord, moeders van zonen die met de Twin Towers ten onder gingen, de weduwe van Gerrit Jan Heijn...'
        . PHP_EOL . 'Zondert twijfel een onvergetelijk avond.'
        . PHP_EOL . 'Graag doorklikken naar de website van Markant Melle Merelbeke voor alle informatie betreffende deze lezing. Iedereen welkom!';

        $formattedEvent = $formatter->formatEvent($eventWithUnwantedLineBreaks);
        $expectedFormatting = array(
            'id' =>'ee7c4030-d69f-4584-b0f2-a700955c7df2',
            'description' => $expectedDescription
        );

        $this->assertEquals($expectedFormatting, $formattedEvent);
    }

    /**
     * @test
     * @dataProvider kansentariefEventInfoProvider
     * @param EventInfo $eventInfo
     * @param array $expectedFormatting
     */
    public function it_should_add_a_kansentarief_column_when_kansentarief_is_included(
        EventInfo $eventInfo,
        array $expectedFormatting
    ) {
        $eventInfoService = $this->getMock(EventInfoServiceInterface::class);
        $eventInfoService
            ->method('getEventInfo')
            ->willReturn($eventInfo);

        $includedProperties = [
            'id',
            'kansentarief'
        ];

        $eventWithTerms = $this->getJSONEventFromFile('event_with_price.json');
        $formatter = new TabularDataEventFormatter($includedProperties, $eventInfoService);
        $formattedEvent = $formatter->formatEvent($eventWithTerms);

        $this->assertEquals($expectedFormatting, $formattedEvent);
    }

    public function it_should_format_contact_and_reservation_urls_when_included_for_export()
    {
        $includedProperties = [
            'id',
            'contactPoint.url',
            'contactPoint.reservations.url'
        ];
        $eventWithContactPoints = $this->getJSONEventFromFile('event_with_contact_and_reservation_urls.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithContactPoints);
        $expectedFormatting = array(
            "id" =>"4e24ac6e-8b95-4be6-b2e7-1892869adde3",
            "contactPoint.url" => "http://du.de\r\nhttp://foo.bar\r\nhttp://www.debijloke.be/concerts/karbido-ensemble",
            "contactPoint.reservations.url" => "http://du.de\r\nhttp://foo.bar",
        );

        $this->assertEquals($expectedFormatting, $formattedEvent);
    }

    public function kansentariefEventInfoProvider()
    {
        return [
            'one card system , single tariff' => [
                'eventInfo' => new EventInfo(
                    [
                        [
                            'price' => '1.5',
                            'cardSystem' => 'UiTPAS Regio Aalst'
                        ]
                    ],
                    [
                        EventAdvantage::KANSENTARIEF()
                    ],
                    [
                        '12 punten: Een voordeel van 12 punten.'
                    ]
                ),
                'expectedFormatting' => [
                    "id" => "d1f0e71d-a9a8-4069-81fb-530134502c58",
                    "kansentarief" => "UiTPAS Regio Aalst: € 1,5",
                ]
            ],
            'one card system , multiple tariffs' => [
                'eventInfo' => new EventInfo(
                    [
                        [
                            'price' => '1.5',
                            'cardSystem' => 'UiTPAS Regio Aalst'
                        ],
                        [
                            'price' => '5',
                            'cardSystem' => 'UiTPAS Regio Aalst'
                        ]
                    ],
                    [
                        EventAdvantage::KANSENTARIEF()
                    ],
                    [
                        '12 punten: Een voordeel van 12 punten.'
                    ]
                ),
                'expectedFormatting' => [
                    "id" => "d1f0e71d-a9a8-4069-81fb-530134502c58",
                    "kansentarief" => "UiTPAS Regio Aalst: € 1,5 / € 5",
                ]
            ],
            'multiple card systems , multiple tariffs' => [
                'eventInfo' => new EventInfo(
                    [
                        [
                            'price' => '1.5',
                            'cardSystem' => 'UiTPAS Regio Aalst'
                        ],
                        [
                            'price' => '5',
                            'cardSystem' => 'UiTPAS Regio Aalst'
                        ],
                        [
                            'price' => '0.50',
                            'cardSystem' => 'UiTPAS Regio Diest'
                        ]
                    ],
                    [
                        EventAdvantage::KANSENTARIEF()
                    ],
                    [
                        '12 punten: Een voordeel van 12 punten.'
                    ]
                ),
                'expectedFormatting' => [
                    "id" => "d1f0e71d-a9a8-4069-81fb-530134502c58",
                    "kansentarief" => "UiTPAS Regio Aalst: € 1,5 / € 5 | UiTPAS Regio Diest: € 0,5",
                ]
            ],
        ];
    }
}
