<?php


namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use CommerceGuys\Intl\Currency\CurrencyRepository;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepository;
use CultuurNet\UDB3\Cdb\CdbId\EventCdbIdExtractor;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Cdb\PriceDescriptionParser;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
use CultuurNet\UDB3\SluggerInterface;
use CultuurNet\UDB3\StringFilter\StringFilterInterface;

class CdbXMLImporterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CdbXMLImporter
     */
    protected $importer;

    /**
     * @var OrganizerServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $organizerManager;

    /**
     * @var PlaceServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $placeManager;

    /**
     * @var SluggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $slugger;

    public function setUp()
    {
        $this->importer = new CdbXMLImporter(
            new CdbXMLItemBaseImporter(),
            new EventCdbIdExtractor(),
            new PriceDescriptionParser(
                new NumberFormatRepository(),
                new CurrencyRepository()
            )
        );
        $this->organizerManager = $this->getMock(OrganizerServiceInterface::class);
        $this->placeManager = $this->getMock(PlaceServiceInterface::class);
        $this->slugger = $this->getMock(SluggerInterface::class);
        date_default_timezone_set('Europe/Brussels');
    }

    /**
     * @param string $fileName
     * @return \stdClass
     */
    private function createJsonEventFromCdbXml($fileName, $version = '3.2')
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        $event = EventItemFactory::createEventFromCdbXml(
            "http://www.cultuurdatabank.com/XMLSchema/CdbXSD/{$version}/FINAL",
            $cdbXml
        );

        $jsonEvent = $this->importer->documentWithCdbXML(
            new \stdClass(),
            $event,
            $this->placeManager,
            $this->organizerManager,
            $this->slugger
        );

        return $jsonEvent;
    }

    /**
     * @test
     */
    public function it_imports_the_publication_info()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_without_email_and_phone_number.cdbxml.xml');

        $this->assertEquals('kgielens@kanker.be', $jsonEvent->creator);
        $this->assertEquals('2014-08-12T14:37:58+02:00', $jsonEvent->created);
        $this->assertEquals('2014-10-21T16:47:23+02:00', $jsonEvent->modified);
        $this->assertEquals('Invoerders Algemeen ', $jsonEvent->publisher);
    }

    /**
     * @test
     */
    public function it_adds_an_email_property_when_cdbxml_has_no_organizer_but_has_contact_with_email()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_email_and_phone_number.cdbxml.xml');

        $this->assertEquals('kgielens@stichtingtegenkanker.be', $jsonEvent->organizer['email'][0]);
    }

    /**
     * @test
     */
    public function it_adds_a_phone_property_when_cdbxml_has_no_organizer_but_has_contact_with_phone_number()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_email_and_phone_number.cdbxml.xml');

        $this->assertEquals('0475 82 21 36', $jsonEvent->organizer['phone'][0]);
    }

    /**
     * @test
     */
    public function it_does_not_add_an_email_property_when_cdbxml_has_no_organizer_or_contact_with_email()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_without_email_and_phone_number.cdbxml.xml');

        $this->assertFalse(array_key_exists('email', $jsonEvent->organizer));
    }

    /**
     * @test
     */
    public function it_does_not_add_a_phone_property_when_cdbxml_has_no_organizer_or_contact_with_phone_number()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_without_email_and_phone_number.cdbxml.xml');

        $this->assertFalse(array_key_exists('phone', $jsonEvent->organizer));
    }

    /**
     * @test
     */
    public function it_adds_the_cdbxml_externalid_attribute_to_the_same_as_property_when_not_CDB()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_non_cdb_externalid.cdbxml.xml');

        $this->assertObjectHasAttribute('sameAs', $jsonEvent);
        $this->assertContains('CC_De_Grote_Post:degrotepost_Evenement_453', $jsonEvent->sameAs);
    }

    /**
     * @test
     */
    public function it_does_not_add_the_cdbxml_externalid_attribute_to_the_same_as_property_when_CDB()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_cdb_externalid.cdbxml.xml');

        $this->assertObjectHasAttribute('sameAs', $jsonEvent);
        $this->assertNotContains('CDB:95b30501-6a70-4cb3-a5c9-4a2eb7003214', $jsonEvent->sameAs);
    }

    /**
     * @test
     */
    public function it_adds_a_reference_to_uit_in_vlaanderen_to_the_same_as_property()
    {
        $slug = 'i_am_a_slug';
        $eventId = '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11';

        $this->slugger
            ->expects($this->once())
            ->method('slug')
            ->willReturn($slug);

        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_cdb_externalid.cdbxml.xml');

        $originalReference = 'http://www.uitinvlaanderen.be/agenda/e/' . $slug . '/' . $eventId;

        $this->assertObjectHasAttribute('sameAs', $jsonEvent);
        $this->assertContains($originalReference, $jsonEvent->sameAs);
    }

    /**
     * @test
     */
    public function it_adds_availability_info()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_non_cdb_externalid.cdbxml.xml');

        $this->assertObjectHasAttribute('availableFrom', $jsonEvent);
        $this->assertEquals('2014-07-25T05:18:22+02:00', $jsonEvent->availableFrom);

        $anotherJsonEvent = $this->createJsonEventFromCdbXml('event_with_cdb_externalid.cdbxml.xml');

        $this->assertObjectHasAttribute('availableFrom', $anotherJsonEvent);
        $this->assertEquals('2014-10-22T00:00:00+02:00', $anotherJsonEvent->availableFrom);
    }

    /**
     * @test
     */
    public function it_adds_a_phone_property_to_contact_point()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_email_and_phone_number.cdbxml.xml');

        $this->assertObjectHasAttribute('contactPoint', $jsonEvent);
        $this->assertEquals(array('0475 82 21 36'), $jsonEvent->contactPoint['phone']);
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_phone_property_to_contact_point()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_just_an_email.cdbxml.xml');

        $this->assertObjectHasAttribute('contactPoint', $jsonEvent);
        $this->assertArrayNotHasKey('phone', $jsonEvent->contactPoint);
    }

    /**
     * @test
     */
    public function it_adds_an_email_property_to_contact_point()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_just_an_email.cdbxml.xml');

        $this->assertObjectHasAttribute('contactPoint', $jsonEvent);
        $this->assertEquals(
            array('kgielens@stichtingtegenkanker.be'),
            $jsonEvent->contactPoint['email']
        );
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_email_property_to_contact_point()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_just_a_phone_number.cdbxml.xml');

        $this->assertObjectHasAttribute('contactPoint', $jsonEvent);
        $this->assertArrayNotHasKey('mail', $jsonEvent->contactPoint);
    }

    /**
     * @test
     */
    public function it_adds_contact_info_urls_to_seeAlso_property()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_email_and_phone_number.cdbxml.xml');

        $this->assertObjectHasAttribute('seeAlso', $jsonEvent);
        $this->assertContains('http://www.rekanto.be', $jsonEvent->seeAlso);
    }

    /**
     * @test
     */
    public function it_adds_a_reservation_url_to_bookingInfo_property()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_reservation_url.cdbxml.xml');

        $this->assertObjectHasAttribute('bookingInfo', $jsonEvent);
        $this->assertEquals('http://brugge.iticketsro.com/ccmechelen/', $jsonEvent->bookingInfo['url']);

        // Reservation url should not have been added to seeAlso.
        $this->assertObjectHasAttribute('seeAlso', $jsonEvent);
        $this->assertNotContains('http://brugge.iticketsro.com/ccmechelen/', $jsonEvent->seeAlso);
    }

    /**
     * @test
     */
    public function it_does_not_add_a_non_reservation_url_to_bookingInfo_property()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_email_and_phone_number.cdbxml.xml');

        $this->assertObjectHasAttribute('bookingInfo', $jsonEvent);
        $this->assertArrayNotHasKey('url', $jsonEvent->bookingInfo);
    }

    /**
     * @test
     */
    public function it_does_not_add_reservation_info_to_contact_point()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_all_kinds_of_contact_info.cdbxml.xml');
        $expectedContactPoint = array(
            'email' => array('john@doe.be'),
            'phone' => array('1234 82 21 36'),
            'url' => array('http://www.rekanto.be'),
        );

        $this->assertObjectHasAttribute('contactPoint', $jsonEvent);
        $this->assertEquals($expectedContactPoint, $jsonEvent->contactPoint);
    }

    /**
     * @test
     */
    public function it_has_a_correct_datetime_when_cdbxml_contains_negative_unix_timestamp()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_negative_timestamp.cdbxml.xml');

        $this->assertObjectHasAttribute('bookingInfo', $jsonEvent);
        $this->assertEquals('1968-12-31T23:00:00+00:00', $jsonEvent->bookingInfo['availabilityStarts']);
        $this->assertEquals('1968-12-31T23:00:00+00:00', $jsonEvent->bookingInfo['availabilityEnds']);
    }

    /**
     * @test
     */
    public function it_does_not_include_duplicate_labels()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_duplicate_labels.cdbxml.xml');

        $this->assertEquals(['enkel'], $jsonEvent->labels);
    }

    /**
     * @test
     */
    public function it_should_import_invisible_keywords_as_hidden_labels()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml(
            'event_with_invisible_keyword.cdbxml.xml'
        );

        $this->assertEquals(['toon mij', 'toon mij ook'], $jsonEvent->labels);
        $this->assertEquals(['verberg mij'], $jsonEvent->hiddenLabels);
    }

    /**
     * @test
     */
    public function it_does_import_an_event_with_semicolons_in_keywords_tag()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml(
            'event_with_semicolon_in_keywords_tag.cdbxml.xml'
        );

        $this->assertEquals(['leren Frans', 'cursus Frans'], $jsonEvent->labels);
    }

    /**
     * @test
     */
    public function it_does_import_an_event_with_semicolons_in_keyword_tag()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml(
            'event_with_semicolon_in_keyword_tag.cdbxml.xml'
        );

        $this->assertEquals(
            ['Franse kennis','leren Frans', 'cursus Frans'],
            $jsonEvent->labels
        );
    }

    /**
     * @test
     */
    public function it_does_not_import_a_new_event_with_semicolons_in_keyword_tag()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml(
            'event_with_semicolon_in_keyword_tag_but_too_new.cdbxml.xml'
        );

        $this->assertEquals(['Franse kennis'], $jsonEvent->labels);
    }

    /**
     * @test
     */
    public function it_should_copy_over_a_known_workflow_status()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_all_kinds_of_contact_info.cdbxml.xml');

        $this->assertEquals('APPROVED', $jsonEvent->workflowStatus);
    }

    /**
     * @test
     */
    public function it_uses_a_properly_formatted_price_description()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_properly_formatted_price_description.cdbxml.xml');

        $this->assertEquals(
            [
                [
                    'category' => 'base',
                    'name' => 'Basistarief',
                    'price' => 12.5,
                    'priceCurrency' => 'EUR',
                ],
                [
                    'name' => 'Met kinderen',
                    'category' => 'tariff',
                    'price' => 20.0,
                    'priceCurrency' => 'EUR',
                ],
                [
                    'name' => 'Senioren',
                    'category' => 'tariff',
                    'price' => 30.0,
                    'priceCurrency' => 'EUR',
                ],
            ],
            $jsonEvent->priceInfo
        );
    }

    /**
     * @test
     */
    public function it_falls_back_to_price_value_without_proper_description()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_without_properly_formatted_price_description.cdbxml.xml');

        $this->assertEquals(
            [
                [
                    'category' => 'base',
                    'name' => 'Basistarief',
                    'price' => 12.5,
                    'priceCurrency' => 'EUR',
                ],
            ],
            $jsonEvent->priceInfo
        );
    }

    /**
     * @test
     * @group issue-III-1636
     */
    public function it_splits_contactinfo_into_contactpoint_and_bookinginfo()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_all_kinds_of_contact_info_2.cdbxml.xml');

        $this->assertEquals(
            [
                'phone' => ['0473233773'],
                'email' => ['bibliotheek@hasselt.be'],
                'url' => ['http://google.be'],
            ],
            $jsonEvent->contactPoint
        );

        $this->assertEquals(
            [
                'phone' => '987654321',
                'email' => 'tickets@test.com',
                'url' => 'http://www.test.be',
                'urlLabel' => 'Reserveer plaatsen',
            ],
            $jsonEvent->bookingInfo
        );
    }

    /**
     * Provides cdbxml with descriptions and the expected UDB3 description.
     */
    public function descriptionsProvider()
    {
        return array(
            'merge short description and long description when short description is not repeated in long description for events' => array(
                'event_with_short_and_long_description.cdbxml.xml',
                'description.txt'
            ),
            'use long description when there is no short description in UDB2' => array(
                'event_without_short_description.cdbxml.xml',
                'description_from_only_long_description.txt',
            ),
            'remove repetition of short description in long description for events ONLY when FULL short description is equal to the first part of long description' => array(
                'event_with_short_description_included_in_long_description.cdbxml.xml',
                'description.txt',
            ),
            'remove repetition of short description in long description for events ONLY when FULL short description is equal to the first part of long description and keep HTML of long description' => array(
                'event_vertelavond_jan_gabriels.cdbxml.xml',
                'description_vertelavond_jan_gabriels.txt',
                '3.3',
            ),
        );
    }

    /**
     * @test
     * @group issue-III-165
     * @dataProvider descriptionsProvider
     */
    public function it_combines_long_and_short_description_to_one_description(
        $cdbxmlFile,
        $expectedDescriptionFile,
        $schemaVersion = '3.2'
    ) {
        $jsonEvent = $this->createJsonEventFromCdbXml($cdbxmlFile, $schemaVersion);

        $this->assertEquals(
            file_get_contents(__DIR__ . '/' . $expectedDescriptionFile),
            $jsonEvent->description['nl']
        );
    }
}
