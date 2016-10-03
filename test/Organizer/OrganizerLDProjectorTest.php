<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBusInterface;
use Broadway\Serializer\SerializableInterface;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Organizer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerUpdatedFromUDB2;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Title;
use stdClass;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

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

    /**
     * @var EventBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventBus;

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelRepository;

    /**
     * @var IriGeneratorInterface
     */
    private $iriGenerator;

    public function setUp()
    {
        $this->documentRepository = $this->getMock(DocumentRepositoryInterface::class);

        $this->eventBus = $this->getMock(EventBusInterface::class);

        $this->iriGenerator = new CallableIriGenerator(
            function ($id) {
                return 'http://example.com/entity/' . $id;
            }
        );

        $this->labelRepository = $this->getMock(ReadRepositoryInterface::class);

        $this->projector = new OrganizerLDProjector(
            $this->documentRepository,
            $this->iriGenerator,
            $this->eventBus,
            $this->labelRepository
        );
    }

    /**
     * @param string $fileName
     * @return OrganizerImportedFromUDB2
     */
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
     * @param string $fileName
     * @return OrganizerUpdatedFromUDB2
     */
    private function organizerUpdatedFromUDB2($fileName)
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        $event = new OrganizerUpdatedFromUDB2(
            'someId',
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        return $event;
    }

    /**
     * @test
     */
    public function it_handles_new_organizers()
    {
        $uuidGenerator = new Version4Generator();
        $id = $uuidGenerator->generate();
        $created = '2015-01-20T13:25:21+01:00';

        $placeCreated = new OrganizerCreated(
            $id,
            new Title('some representative title'),
            [new Address('$street', '$postalCode', '$locality', '$country')],
            ['050/123'],
            ['test@test.be', 'test2@test.be'],
            ['http://www.google.be']
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/api/1.0/organizer.jsonld';
        $jsonLD->name = 'some representative title';
        $jsonLD->addresses = [
            [
                'addressCountry' => '$country',
                'addressLocality' => '$locality',
                'postalCode' => '$postalCode',
                'streetAddress' => '$street',
            ]
        ];
        $jsonLD->phone = ['050/123'];
        $jsonLD->email = ['test@test.be', 'test2@test.be'];
        $jsonLD->url = ['http://www.google.be'];
        $jsonLD->created = $created;

        $expectedDocument = (new JsonDocument($id))
            ->withBody($jsonLD);

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($expectedDocument);

        $this->projector->handle(
            new DomainMessage(
                1,
                1,
                new Metadata(),
                $placeCreated,
                BroadwayDateTime::fromString($created)
            )
        );
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
    public function it_adds_a_phone_property_when_cdbxml_has_multiple_phone_numbers()
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

    /**
     * @test
     */
    public function it_deletes_an_organizer()
    {
        $organizerId = 'ORG-123-FOO';
        $organizerDeleted = new OrganizerDeleted($organizerId);

        $this->documentRepository->expects($this->once())
            ->method('remove')
            ->with($organizerId);

        $this->projector->applyOrganizerDeleted($organizerDeleted);
    }

    /**
     * @test
     */
    public function it_can_update_an_organizer_from_udb2_even_if_it_has_been_deleted()
    {
        $organizerUpdatedFromUdb2 = $this->organizerUpdatedFromUDB2('organizer_with_email.cdbxml.xml');
        $actorId = $organizerUpdatedFromUdb2->getActorId();

        $this->documentRepository->expects($this->once())
            ->method('get')
            ->with($actorId)
            ->willThrowException(new DocumentGoneException());

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) use ($actorId) {
                        return $actorId === $jsonDocument->getId() && !empty($jsonDocument->getRawBody());
                    }
                )
            );

        $this->projector->applyOrganizerUpdatedFromUDB2($organizerUpdatedFromUdb2);
    }

    /**
     * @test
     */
    public function it_handles_label_added()
    {
        $organizerId = '586f596d-7e43-4ab9-b062-04db9436fca4';
        $labelId = new UUID('00a91b64-e9f8-4213-a4a7-a21d633e65d6');

        $this->mockGet($organizerId, 'organizer.json');

        $label = new Entity(
            $labelId,
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC()
        );
        $this->labelRepository->method('getByUuid')
            ->with($labelId)
            ->willReturn($label);

        $labelAdded = new LabelAdded($organizerId, $labelId);
        $domainMessage = $this->createDomainMessage($labelAdded);

        $this->expectSave($organizerId, 'organizer_with_label.json');

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     * @dataProvider labelRemovedDataProvider
     * @param UUID $labelId
     * @param string $originalFile
     * @param string $finalFile
     */
    public function it_handles_label_removed(
        UUID $labelId,
        $originalFile,
        $finalFile
    ) {
        $organizerId = '586f596d-7e43-4ab9-b062-04db9436fca4';

        $this->mockGet($organizerId, $originalFile);

        $labelRemoved = new LabelRemoved($organizerId, $labelId);
        $domainMessage = $this->createDomainMessage($labelRemoved);

        $this->expectSave($organizerId, $finalFile);

        $this->projector->handle($domainMessage);
    }

    public function labelRemovedDataProvider()
    {
        return [
            [
                new UUID('00a91b64-e9f8-4213-a4a7-a21d633e65d6'),
                'organizer_with_label.json',
                'organizer.json'
            ],
            [
                new UUID('8e382f93-843b-4e7a-af9a-5cf213df5b9a'),
                'organizer_with_multiple_labels.json',
                'organizer_with_label.json'
            ]
        ];
    }

    /**
     * @param string $organizerId
     * @param string $fileName
     */
    private function mockGet($organizerId, $fileName)
    {
        $organizerJson = file_get_contents(__DIR__ . '/Samples/' . $fileName);
        $this->documentRepository->method('get')
            ->with($organizerId)
            ->willReturn(new JsonDocument($organizerId, $organizerJson));
    }

    /**
     * @param string $organizerId
     * @param string $fileName
     */
    private function expectSave($organizerId, $fileName)
    {
        $organizerWithLabelJson = file_get_contents(__DIR__ . '/Samples/' . $fileName);
        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with(new JsonDocument($organizerId, $organizerWithLabelJson));
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @return DomainMessage
     */
    private function createDomainMessage(AbstractLabelEvent $labelEvent)
    {
        return new DomainMessage(
            $labelEvent->getOrganizerId(),
            0,
            new Metadata(),
            $labelEvent,
            BroadwayDateTime::now()
        );
    }
}
