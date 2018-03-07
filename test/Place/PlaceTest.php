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
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Place\Events\AddressTranslated;
use CultuurNet\UDB3\Place\Events\AddressUpdated;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
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
     */
    public function it_handles_update_facilities_after_udb2_import()
    {
        $placeId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';

        $address = new Address(
            new Street('Eenmeilaan'),
            new PostalCode('3010'),
            new Locality('Kessel-Lo'),
            Country::fromNative('BE')
        );

        $placeCreated = new PlaceCreated(
            $placeId,
            new Title('Test place'),
            new EventType('0.1.1', 'Jeugdhuis'),
            $address,
            new Calendar(CalendarType::PERMANENT())
        );

        $facilities = [
            new Facility("3.27.0.0.0", "Rolstoeltoegankelijk"),
            new Facility("3.30.0.0.0", "Rolstoelpodium")
        ];

        $cdbXml = $this->getCdbXML('/ReadModel/JSONLD/place_with_long_description.cdbxml.xml');
        $cdbXmlNamespace = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';

        $this->scenario
            ->given(
                [
                    $placeCreated,
                    new FacilitiesUpdated($placeId, $facilities),
                    new PlaceUpdatedFromUDB2($placeId, $cdbXml, $cdbXmlNamespace),
                ]
            )
            ->when(
                function (Place $place) use ($facilities) {
                    $place->updateFacilities($facilities);
                }
            )
            ->then(
                [
                    new FacilitiesUpdated($placeId, $facilities),
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
    public function it_should_update_the_address_in_the_main_language(
        Address $originalAddress,
        Address $updatedAddress
    ) {
        $language = new Language('nl');

        $this->scenario
            ->withAggregateId('c5c1b435-0f3c-4b75-9f28-94d93be7078b')
            ->given(
                [
                    new PlaceCreated(
                        'c5c1b435-0f3c-4b75-9f28-94d93be7078b',
                        new Title('Test place'),
                        new EventType('0.1.1', 'Jeugdhuis'),
                        $originalAddress,
                        new Calendar(CalendarType::PERMANENT())
                    ),
                ]
            )
            ->when(
                function (Place $place) use ($updatedAddress, $language) {
                    $place->updateAddress($updatedAddress, $language);
                }
            )
            ->then(
                [
                    new AddressUpdated('c5c1b435-0f3c-4b75-9f28-94d93be7078b', $updatedAddress),
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_not_update_the_address_when_address_is_not_changed()
    {
        $address = new Address(
            new Street('Eenmeilaan'),
            new PostalCode('3010'),
            new Locality('Kessel-Lo'),
            Country::fromNative('BE')
        );

        $translatedAddress = new Address(
            new Street('One May Street'),
            new PostalCode('3010'),
            new Locality('Kessel-High'),
            Country::fromNative('BE')
        );

        $this->scenario
            ->withAggregateId('c5c1b435-0f3c-4b75-9f28-94d93be7078b')
            ->given(
                [
                    new PlaceCreated(
                        'c5c1b435-0f3c-4b75-9f28-94d93be7078b',
                        new Title('Test place'),
                        new EventType('0.1.1', 'Jeugdhuis'),
                        $address,
                        new Calendar(CalendarType::PERMANENT())
                    ),
                ]
            )
            ->when(
                function (Place $place) use ($address, $translatedAddress) {
                    $place->updateAddress($address, new Language('nl'));
                    $place->updateAddress($translatedAddress, new Language('en'));
                    $place->updateAddress($translatedAddress, new Language('en'));
                }
            )
            ->then([
                new AddressTranslated('c5c1b435-0f3c-4b75-9f28-94d93be7078b', $translatedAddress, new Language('en')),
            ]);
    }

    /**
     * @test
     */
    public function it_should_update_the_address_after_udb2_updates()
    {
        $address = new Address(
            new Street('Eenmeilaan'),
            new PostalCode('3010'),
            new Locality('Kessel-Lo'),
            Country::fromNative('BE')
        );

        $cdbXml = $this->getCdbXML('/ReadModel/JSONLD/place_with_same_address.xml');
        $cdbNamespace = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL';

        $this->scenario
            ->withAggregateId('c5c1b435-0f3c-4b75-9f28-94d93be7078b')
            ->given(
                [
                    new PlaceCreated(
                        'c5c1b435-0f3c-4b75-9f28-94d93be7078b',
                        new Title('Test place'),
                        new EventType('GnPFp9uvOUyqhOckIFMKmg', 'Museum of galerij'),
                        $address,
                        new Calendar(CalendarType::PERMANENT())
                    ),
                ]
            )
            ->when(
                function (Place $place) use ($address, $cdbXml, $cdbNamespace) {
                    $place->updateAddress($address, new Language('nl'));
                    $place->updateWithCdbXml($cdbXml, $cdbNamespace);
                    $place->updateAddress($address, new Language('nl'));
                }
            )
            ->then([
                new PlaceUpdatedFromUDB2(
                    'c5c1b435-0f3c-4b75-9f28-94d93be7078b',
                    $cdbXml,
                    $cdbNamespace
                ),
                new AddressUpdated(
                    'c5c1b435-0f3c-4b75-9f28-94d93be7078b',
                    $address
                ),
            ]);
    }

    /**
     * @test
     * @dataProvider updateAddressDataProvider
     *
     * @param Address $originalAddress
     * @param Address $updatedAddress
     */
    public function it_should_translate_the_address_in_any_other_language_than_the_main_language(
        Address $originalAddress,
        Address $updatedAddress
    ) {
        $language = new Language('fr');

        $this->scenario
            ->withAggregateId('c5c1b435-0f3c-4b75-9f28-94d93be7078b')
            ->given(
                [
                    new PlaceCreated(
                        'c5c1b435-0f3c-4b75-9f28-94d93be7078b',
                        new Title('Test place'),
                        new EventType('0.1.1', 'Jeugdhuis'),
                        $originalAddress,
                        new Calendar(CalendarType::PERMANENT())
                    ),
                ]
            )
            ->when(
                function (Place $place) use ($updatedAddress, $language) {
                    $place->updateAddress($updatedAddress, $language);
                }
            )
            ->then(
                [
                    new AddressTranslated('c5c1b435-0f3c-4b75-9f28-94d93be7078b', $updatedAddress, $language),
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
                    ),
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
                    ),
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
                    ),
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
