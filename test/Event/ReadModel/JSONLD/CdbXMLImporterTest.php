<?php


namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use CultuurNet\UDB3\SluggerInterface;

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
        $this->importer = new CdbXMLImporter();
        $this->organizerManager = $this->getMock(OrganizerServiceInterface::class);
        $this->placeManager = $this->getMock(PlaceServiceInterface::class);
        $this->slugger = $this->getMock(SluggerInterface::class);
        date_default_timezone_set('Europe/Brussels');
    }

    private function createJsonEventFromCdbXml($fileName)
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        $event = EventItemFactory::createEventFromCdbXml(
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL',
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
    public function it_filters_the_description_property_when_filters_are_added()
    {
        /** @var PlaceServiceInterface|\PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMock(StringFilterInterface::class);
        $filter->expects($this->atLeastOnce())
            ->method('filter');

        $this->importer->addDescriptionFilter($filter);

        $this->createJsonEventFromCdbXml('event_with_email_and_phone_number.cdbxml.xml');
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

        $this->assertObjectHasAttribute('available', $jsonEvent);
        $this->assertEquals('2014-07-25T05:18:22+02:00', $jsonEvent->available);

        $anotherJsonEvent = $this->createJsonEventFromCdbXml('event_with_cdb_externalid.cdbxml.xml');

        $this->assertObjectHasAttribute('available', $anotherJsonEvent);
        $this->assertEquals('2014-10-22T00:00:00+02:00', $anotherJsonEvent->available);
    }

    /**
     * @test
     */
    public function it_adds_a_telephone_property_to_contact_point()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_email_and_phone_number.cdbxml.xml');

        $this->assertObjectHasAttribute('contactPoint', $jsonEvent);
        $this->assertEquals('0475 82 21 36', $jsonEvent->contactPoint[0]['telephone'][0]);
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_telephone_property_to_contact_point()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_just_an_email.cdbxml.xml');

        $this->assertObjectHasAttribute('contactPoint', $jsonEvent);
        $this->assertTrue(is_array($jsonEvent->contactPoint));
        $this->assertArrayNotHasKey('telephone', $jsonEvent->contactPoint[0]);
    }

    /**
     * @test
     */
    public function it_adds_an_email_property_to_contact_point()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_just_an_email.cdbxml.xml');

        $this->assertObjectHasAttribute('contactPoint', $jsonEvent);
        $this->assertTrue(is_array($jsonEvent->contactPoint));
        $this->assertEquals('kgielens@stichtingtegenkanker.be', $jsonEvent->contactPoint[0]['email'][0]);
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_email_property_to_contact_point()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_just_a_phone_number.cdbxml.xml');

        $this->assertObjectHasAttribute('contactPoint', $jsonEvent);
        $this->assertTrue(is_array($jsonEvent->contactPoint));
        $this->assertArrayNotHasKey('email', $jsonEvent->contactPoint[0]);
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
        $this->assertEquals('http://brugge.iticketsro.com/ccmechelen/', $jsonEvent->bookingInfo[0]['url']);

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
        $this->assertArrayNotHasKey('url', $jsonEvent->bookingInfo[0]);
    }

    /**
     * @test
     */
    public function it_creates_a_separate_contact_point_for_reservation_info()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_all_kinds_of_contact_info.cdbxml.xml');
        $expectedContactPoints = array(
            array(
                'email' => array('kgielens@stichtingtegenkanker.be'),
                'telephone' => array('0475 82 21 36'),
                'contactType' => 'Reservations'
            ),
            array(
                'email' => array('john@doe.be'),
                'telephone' => array('1234 82 21 36')
            )
        );

        $this->assertObjectHasAttribute('contactPoint', $jsonEvent);
        $this->assertEquals($expectedContactPoints, $jsonEvent->contactPoint);
    }

    /**
     * @test
     */
    public function it_has_a_correct_datetime_when_cdbxml_contains_negative_unix_timestamp()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_negative_timestamp.cdbxml.xml');

        $this->assertObjectHasAttribute('bookingInfo', $jsonEvent);
        $this->assertEquals('1968-12-31T23:00:00+00:00', $jsonEvent->bookingInfo[0]['availabilityStarts']);
        $this->assertEquals('1968-12-31T23:00:00+00:00', $jsonEvent->bookingInfo[0]['availabilityEnds']);
    }

    /**
     * @test
     */
    public function it_does_not_include_duplicate_labels()
    {
        $jsonEvent = $this->createJsonEventFromCdbXml('event_with_duplicate_labels.cdbxml.xml');

        $this->assertEquals(['enkel'], $jsonEvent->labels);
    }
}
