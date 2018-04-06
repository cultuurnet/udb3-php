<?php

namespace CultuurNet\UDB3\Place;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Place\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Place\Events\PriceInfoUpdated;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\AgeRange;
use CultuurNet\UDB3\Place\Events\AddressTranslated;
use CultuurNet\UDB3\Place\Events\AddressUpdated;
use CultuurNet\UDB3\Place\Events\CalendarUpdated;
use CultuurNet\UDB3\Place\Events\ContactPointUpdated;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\PriceInfo\BasePrice;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use CultuurNet\UDB3\Title;
use ValueObjects\Geography\Country;
use ValueObjects\Money\Currency;
use ValueObjects\Person\Age;

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
    public function it_handles_update_facilities_after_udb2_update()
    {
        $placeCreated = $this->createPlaceCreatedEvent();
        $placeId = $placeCreated->getPlaceId();

        $facilities = [
            new Facility("3.27.0.0.0", "Rolstoeltoegankelijk"),
            new Facility("3.30.0.0.0", "Rolstoelpodium"),
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
     */
    public function it_handles_update_contact_point_after_udb2_import()
    {
        $placeCreated = $this->createPlaceCreatedEvent();
        $placeId = $placeCreated->getPlaceId();

        $contactPoint = new ContactPoint(
            ['016/101010',],
            ['test@2dotstwice.be', 'admin@2dotstwice.be'],
            ['http://www.2dotstwice.be']
        );

        $cdbXml = $this->getCdbXML('/ReadModel/JSONLD/place_with_long_description.cdbxml.xml');
        $cdbXmlNamespace = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';

        $this->scenario
            ->given(
                [
                    $placeCreated,
                    new ContactPointUpdated($placeId, $contactPoint),
                    new PlaceUpdatedFromUDB2($placeId, $cdbXml, $cdbXmlNamespace),
                ]
            )
            ->when(
                function (Place $place) use ($contactPoint) {
                    $place->updateContactPoint($contactPoint);
                }
            )
            ->then(
                [
                    new ContactPointUpdated($placeId, $contactPoint),
                ]
            );
    }

    /**
     * @test
     */
    public function it_handles_update_calendar_after_udb2_import()
    {
        $placeCreated = $this->createPlaceCreatedEvent();
        $placeId = $placeCreated->getPlaceId();

        $calendar = new Calendar(
            CalendarType::SINGLE(),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-26T11:11:11+01:00'),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-27T12:12:12+01:00')
        );

        $cdbXml = $this->getCdbXML('/ReadModel/JSONLD/place_with_long_description.cdbxml.xml');
        $cdbXmlNamespace = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';

        $this->scenario
            ->given(
                [
                    $placeCreated,
                    new CalendarUpdated($placeId, $calendar),
                    new PlaceUpdatedFromUDB2($placeId, $cdbXml, $cdbXmlNamespace),
                ]
            )
            ->when(
                function (Place $place) use ($calendar) {
                    $place->updateCalendar($calendar);
                }
            )
            ->then(
                [
                    new CalendarUpdated($placeId, $calendar),
                ]
            );
    }

    /**
     * @test
     */
    public function it_handles_update_price_info_after_udb2_import()
    {
        $placeCreated = $this->createPlaceCreatedEvent();
        $placeId = $placeCreated->getPlaceId();

        $priceInfo = new PriceInfo(
            new BasePrice(
                new Price(1000),
                Currency::fromNative('EUR')
            )
        );

        $cdbXml = $this->getCdbXML('/ReadModel/JSONLD/place_with_long_description.cdbxml.xml');
        $cdbXmlNamespace = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';

        $this->scenario
            ->given(
                [
                    $placeCreated,
                    new PriceInfoUpdated($placeId, $priceInfo),
                    new PlaceUpdatedFromUDB2($placeId, $cdbXml, $cdbXmlNamespace),
                ]
            )
            ->when(
                function (Place $place) use ($priceInfo) {
                    $place->updatePriceInfo($priceInfo);
                }
            )
            ->then(
                [
                    new PriceInfoUpdated($placeId, $priceInfo),
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
                        new Language('nl'),
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
        $placeCreated = $this->createPlaceCreatedEvent();
        $placeId = $placeCreated->getPlaceId();
        $address = $placeCreated->getAddress();

        $translatedAddress = new Address(
            new Street('One May Street'),
            new PostalCode('3010'),
            new Locality('Kessel-High'),
            Country::fromNative('BE')
        );

        $this->scenario
            ->withAggregateId($placeId)
            ->given(
                [
                    $placeCreated,
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
                new AddressTranslated($placeId, $translatedAddress, new Language('en')),
            ]);
    }

    /**
     * @test
     */
    public function it_should_update_the_address_after_udb2_updates()
    {
        $placeCreated = $this->createPlaceCreatedEvent();
        $placeId = $placeCreated->getPlaceId();

        $address = new Address(
            new Street('Eenmeilaan'),
            new PostalCode('3010'),
            new Locality('Kessel-Lo'),
            Country::fromNative('BE')
        );

        $cdbXml = $this->getCdbXML('/ReadModel/JSONLD/place_with_same_address.xml');
        $cdbNamespace = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL';

        $this->scenario
            ->withAggregateId($placeId)
            ->given(
                [
                    $placeCreated,
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
                    $placeId,
                    $cdbXml,
                    $cdbNamespace
                ),
                new AddressUpdated(
                    $placeId,
                    $address
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_handles_update_typical_age_range_after_udb2_update()
    {
        $placeCreated = $this->createPlaceCreatedEvent();
        $placeId = $placeCreated->getPlaceId();

        $typicalAgeRange = new AgeRange(new Age(8), new Age(11));
        $otherTypicalAgeRange = new AgeRange(new Age(9), new Age(11));

        $cdbXml = $this->getCdbXML('/ReadModel/JSONLD/place_with_long_description.cdbxml.xml');
        $cdbXmlNamespace = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';

        $this->scenario
            ->given(
                [
                    $placeCreated,
                    new TypicalAgeRangeUpdated($placeId, $typicalAgeRange),
                    new PlaceUpdatedFromUDB2($placeId, $cdbXml, $cdbXmlNamespace),
                ]
            )
            ->when(
                function (Place $place) use ($typicalAgeRange, $otherTypicalAgeRange) {
                    $place->updateTypicalAgeRange($typicalAgeRange);
                    $place->updateTypicalAgeRange($otherTypicalAgeRange);
                }
            )
            ->then(
                [
                    new TypicalAgeRangeUpdated($placeId, $otherTypicalAgeRange),
                ]
            );
    }

    /**
     * @test
     */
    public function it_handles_delete_typical_age_range_after_udb2_update()
    {
        $placeCreated = $this->createPlaceCreatedEvent();
        $placeId = $placeCreated->getPlaceId();

        $typicalAgeRange = new AgeRange(new Age(8), new Age(11));

        $cdbXml = $this->getCdbXML('/ReadModel/JSONLD/place_with_long_description.cdbxml.xml');
        $cdbXmlNamespace = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';

        $this->scenario
            ->given(
                [
                    $placeCreated,
                    new TypicalAgeRangeUpdated($placeId, $typicalAgeRange),
                    new PlaceUpdatedFromUDB2($placeId, $cdbXml, $cdbXmlNamespace),
                ]
            )
            ->when(
                function (Place $place) use ($typicalAgeRange) {
                    $place->deleteTypicalAgeRange();
                }
            )
            ->then(
                [
                    new TypicalAgeRangeDeleted($placeId),
                ]
            );
    }

    /**
     * @test
     */
    public function it_handles_update_booking_info_after_udb2_import()
    {
        $placeCreated = $this->createPlaceCreatedEvent();
        $placeId = $placeCreated->getPlaceId();

        $bookingInfo = new BookingInfo(
            'www.publiq.be',
            'publiq',
            '02 123 45 67',
            'info@publiq.be'
        );

        $cdbXml = $this->getCdbXML('/ReadModel/JSONLD/place_with_long_description.cdbxml.xml');
        $cdbXmlNamespace = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';

        $this->scenario
            ->given(
                [
                    $placeCreated,
                    new BookingInfoUpdated($placeId, $bookingInfo),
                    new PlaceUpdatedFromUDB2($placeId, $cdbXml, $cdbXmlNamespace),
                ]
            )
            ->when(
                function (Place $place) use ($bookingInfo) {
                    $place->updateBookingInfo($bookingInfo);
                }
            )
            ->then(
                [
                    new BookingInfoUpdated($placeId, $bookingInfo),
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
                        new Language('nl'),
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

    /**
     * @return PlaceCreated
     */
    private function createPlaceCreatedEvent()
    {
        $placeId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';

        $address = new Address(
            new Street('Eenmeilaan'),
            new PostalCode('3010'),
            new Locality('Kessel-Lo'),
            Country::fromNative('BE')
        );

        return  new PlaceCreated(
            $placeId,
            new Language('nl'),
            new Title('Test place'),
            new EventType('0.1.1', 'Jeugdhuis'),
            $address,
            new Calendar(CalendarType::PERMANENT())
        );
    }
}
