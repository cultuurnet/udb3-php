<?php


namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\OrganizerServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\PlaceServiceInterface;
use CultuurNet\UDB3\Event\EventImportedFromUDB2;

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

    public function setUp()
    {
        $this->importer = new CdbXMLImporter();
        $this->organizerManager = $this->getMock(OrganizerServiceInterface::class);
        $this->placeManager = $this->getMock(PlaceServiceInterface::class);
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
            $this->organizerManager
        );

        return $jsonEvent;
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

        $this->assertObjectNotHasAttribute('sameAs', $jsonEvent);
    }
}
