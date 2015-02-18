<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\EventHandling\EventBusInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;

class OrganizerLDProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrganizerLDProjector
     */
    protected $projector;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentRepository;

    public function setUp()
    {
        $this->documentRepository = $this->getMock(DocumentRepositoryInterface::class);
        $this->projector = new OrganizerLDProjector(
            $this->documentRepository,
            $this->getMock(IriGeneratorInterface::class),
            $this->getMock(EventBusInterface::class)
        );
    }

    private function organizerImportedFromUDB2($fileName)
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/' . $fileName
        );
        $event = new OrganizerImportedFromUDB2(
            'someId',
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        return $event;
    }

    /**
     * @test
     */
    public function it_adds_an_email_property_when_cdbxml_has_an_email()
    {
        $event = $this->organizerImportedFromUDB2('organizer_with_email.cdbxml.xml');

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (JsonDocument $document) {
                $body = $document->getBody();

                $emails = $body->email;
                $expectedEmails = [
                    'info@villanella.be'
                ];

                return is_array($emails) &&
                $emails == $expectedEmails;
            }));

        $this->projector->applyOrganizerImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_does_not_add_an_email_property_when_cdbxml_has_no_email()
    {
        $event = $this->organizerImportedFromUDB2('organizer_without_email.cdbxml.xml');

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (JsonDocument $document) {
                $body = $document->getBody();

                return !property_exists($body, 'email');
            }));

        $this->projector->applyOrganizerImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_adds_an_email_property_when_cdbxml_has_multiple_emails()
    {
        $event = $this->organizerImportedFromUDB2('organizer_with_emails.cdbxml.xml');

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (JsonDocument $document) {
                $body = $document->getBody();

                $emails = $body->email;
                $expectedEmails = [
                    'info@villanella.be',
                    'dirk@dirkinc.be'
                ];

                return is_array($emails) &&
                $emails == $expectedEmails;
            }));

        $this->projector->applyOrganizerImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_adds_a_phone_property_when_cdbxml_has_a_phone_number()
    {
        $event = $this->organizerImportedFromUDB2('organizer_with_phone_number.cdbxml.xml');

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (JsonDocument $document) {
                $body = $document->getBody();

                $phones = $body->phone;
                $expectedPhones = [
                    '+32 3 260 96 10'
                ];

                return is_array($phones) &&
                $phones == $expectedPhones;
            }));

        $this->projector->applyOrganizerImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_adds_a_phone_property_when_cdbxml_has_multiple_phone_numbers(
    )
    {
        $event = $this->organizerImportedFromUDB2('organizer_with_phone_numbers.cdbxml.xml');

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (JsonDocument $document) {
                $body = $document->getBody();

                $phones = $body->phone;
                $expectedPhones = [
                    '+32 3 260 96 10',
                    '+32 3 062 69 01'
                ];

                return is_array($phones) &&
                $phones == $expectedPhones;
            }));

        $this->projector->applyOrganizerImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_does_not_add_a_phone_property_when_cdbxml_has_no_phone()
    {
        $event = $this->organizerImportedFromUDB2('organizer_without_phone_number.cdbxml.xml');

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (JsonDocument $document) {
                $body = $document->getBody();

                return !property_exists($body, 'phone');
            }));

        $this->projector->applyOrganizerImportedFromUDB2($event);
    }
}
