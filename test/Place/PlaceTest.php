<?php

namespace CultuurNet\UDB3\Place;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;
use CultuurNet\UDB3\Title;
use ValueObjects\Geography\Country;

class PlaceTest extends AggregateRootScenarioTestCase
{
    /**
     * Returns a string representing the aggregate root
     *
     * @return string AggregateRoot
     */
    protected function getAggregateRootClass()
    {
        return Place::class;
    }

    private function getCdbXML($filename)
    {
        return file_get_contents(
            __DIR__ . $filename
        );
    }

    /**
     * @test
     * @dataProvider updateAddressDataProvider
     *
     * @param Address $originalAddress
     * @param Address $updatedAddress
     */
    public function it_should_update_the_address_on_a_newly_created_place(
        Address $originalAddress,
        Address $updatedAddress
    ) {
        $this->scenario
            ->withAggregateId('a3ac59a1-eba3-4071-b765-6b38bec74a62')
            ->given(
                [
                    new PlaceCreated(
                        'a3ac59a1-eba3-4071-b765-6b38bec74a62',
                        new Title('JH Sojo'),
                        new EventType('0.1.1', 'Jeugdhuis'),
                        $originalAddress,
                        new Calendar(CalendarType::PERMANENT())
                    ),
                ]
            )
            ->when(
                function (Place $place) use ($updatedAddress) {
                    $place->updateAddress($updatedAddress);
                }
            )
            ->then(
                [
                    new MajorInfoUpdated(
                        'a3ac59a1-eba3-4071-b765-6b38bec74a62',
                        new Title('JH Sojo'),
                        new EventType('0.1.1', 'Jeugdhuis'),
                        $updatedAddress,
                        new Calendar(CalendarType::PERMANENT())
                    ),
                ]
            );
    }

    /**
     * @test
     * @dataProvider updateAddressDataProvider
     *
     * @param Address $originalAddress
     * @param Address $updatedAddress
     */
    public function it_should_update_the_address_on_a_place_that_had_its_major_info_updated(
        Address $originalAddress,
        Address $updatedAddress
    ) {
        $this->scenario
            ->withAggregateId('a3ac59a1-eba3-4071-b765-6b38bec74a62')
            ->given(
                [
                    new PlaceCreated(
                        'a3ac59a1-eba3-4071-b765-6b38bec74a62',
                        new Title('JH Sojo'),
                        new EventType('0.1.1', 'Jeugdhuis'),
                        $originalAddress,
                        new Calendar(CalendarType::PERMANENT())
                    ),
                    new MajorInfoUpdated(
                        'a3ac59a1-eba3-4071-b765-6b38bec74a62',
                        new Title('JH Sojo UPDATED'),
                        new EventType('0.1.1.2', 'Jeugdhuis en jeugdcentrum'),
                        $originalAddress,
                        new Calendar(
                            CalendarType::SINGLE(),
                            \DateTimeImmutable::createFromFormat(
                                \DateTime::ATOM,
                                '2017-06-09T16:00:00+02:00'
                            ),
                            \DateTimeImmutable::createFromFormat(
                                \DateTime::ATOM,
                                '2017-06-09T22:00:00+02:00'
                            )
                        )
                    ),
                ]
            )
            ->when(
                function (Place $place) use ($updatedAddress) {
                    $place->updateAddress($updatedAddress);
                }
            )
            ->then(
                [
                    new MajorInfoUpdated(
                        'a3ac59a1-eba3-4071-b765-6b38bec74a62',
                        new Title('JH Sojo UPDATED'),
                        new EventType('0.1.1.2', 'Jeugdhuis en jeugdcentrum'),
                        $updatedAddress,
                        new Calendar(
                            CalendarType::SINGLE(),
                            \DateTimeImmutable::createFromFormat(
                                \DateTime::ATOM,
                                '2017-06-09T16:00:00+02:00'
                            ),
                            \DateTimeImmutable::createFromFormat(
                                \DateTime::ATOM,
                                '2017-06-09T22:00:00+02:00'
                            )
                        )
                    ),
                ]
            );
    }

    /**
     * @return array
     */
    public function updateAddressDataProvider()
    {
        return [
            [
                'original' => new Address(
                    new Street('Eenmeilaan'),
                    new PostalCode('3010'),
                    new Locality('Kessel-Lo'),
                    Country::fromNative('BE')
                ),
                'updated' => new Address(
                    new Street('Eenmeilaan 35'),
                    new PostalCode('3010'),
                    new Locality('Kessel-Lo'),
                    Country::fromNative('BE')
                ),
            ],
        ];
    }

    /**
     * @test
     */
    public function it_imports_from_udb2_actors_and_takes_keywords_into_account()
    {
        $cdbXml = $this->getCdbXML(
            '/ReadModel/JSONLD/place_with_long_description.cdbxml.xml'
        );

        $this->scenario
            ->when(
                function () use ($cdbXml) {
                    return Place::importFromUDB2Actor(
                        '318F2ACB-F612-6F75-0037C9C29F44087A',
                        $cdbXml,
                        'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                    );
                }
            )
            ->then(
                [
                    new PlaceImportedFromUDB2(
                        '318F2ACB-F612-6F75-0037C9C29F44087A',
                        $cdbXml,
                        'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                    )
                ]
            )
            ->when(
                function (Place $place) {
                    $place->addLabel(new Label('Toevlalocatie'));
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_applies_placeUpdatedFromUdb2_when_updating_actor_cdbxml_and_takes_keywords_into_account()
    {
        $cdbXml = $this->getCdbXML(
            '/ReadModel/JSONLD/place_with_long_description.cdbxml.xml'
        );

        $this->scenario
            ->withAggregateId('318F2ACB-F612-6F75-0037C9C29F44087A')
            ->given(
                [
                    new PlaceImportedFromUDB2(
                        '318F2ACB-F612-6F75-0037C9C29F44087A',
                        $cdbXml,
                        'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                    )
                ]
            )
            ->when(
                function (Place $place) use ($cdbXml) {
                    $place->updateWithCdbXml($cdbXml, 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL');
                }
            )
            ->then(
                [
                    new PlaceUpdatedFromUDB2(
                        '318F2ACB-F612-6F75-0037C9C29F44087A',
                        $cdbXml,
                        'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                    )
                ]
            )
            ->when(
                function (Place $place) {
                    $place->addLabel(new Label('Toevlalocatie'));
                }
            )
            ->then([]);
    }

    /**
     * @test
     * @dataProvider newPlaceProvider
     *
     * @param string                    $expectedId The unique id of the place element
     * @param EventSourcedAggregateRoot $place
     */
    public function it_has_an_id($expectedId, $place)
    {
        $this->assertEquals($expectedId, $place->getAggregateRootId());
    }

    /**
     * @return array
     */
    public function newPlaceProvider()
    {
        return [
            'actor' => [
                '318F2ACB-F612-6F75-0037C9C29F44087A',
                Place::importFromUDB2Actor(
                    '318F2ACB-F612-6F75-0037C9C29F44087A',
                    $this->getCdbXML(
                        '/ReadModel/JSONLD/place_with_long_description.cdbxml.xml'
                    ),
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                ),
            ],
        ];
    }
}
