<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBusInterface;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Organizer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerEvent;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerUpdatedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\TitleTranslated;
use CultuurNet\UDB3\Organizer\Events\TitleUpdated;
use CultuurNet\UDB3\Organizer\Events\WebsiteUpdated;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Title;
use ValueObjects\Geography\Country;
use ValueObjects\Web\Url;

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
     * @var IriGeneratorInterface
     */
    private $iriGenerator;

    public function setUp()
    {
        $this->documentRepository = $this->createMock(DocumentRepositoryInterface::class);

        $this->eventBus = $this->createMock(EventBusInterface::class);

        $this->iriGenerator = new CallableIriGenerator(
            function ($id) {
                return 'http://example.com/entity/' . $id;
            }
        );

        $this->projector = new OrganizerLDProjector(
            $this->documentRepository,
            $this->iriGenerator,
            $this->eventBus
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

        $street = new Street('Kerkstraat 69');
        $locality = new Locality('Leuven');
        $postalCode = new PostalCode('3000');
        $country = Country::fromNative('BE');

        $organizerCreated = new OrganizerCreated(
            $id,
            new Title('some representative title'),
            [new Address($street, $postalCode, $locality, $country)],
            ['050/123'],
            ['test@test.be', 'test2@test.be'],
            ['http://www.google.be']
        );

        $jsonLD = new \stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/contexts/organizer';
        $jsonLD->name['nl'] = 'some representative title';
        $jsonLD->addresses = [
            [
                'addressCountry' => $country,
                'addressLocality' => $locality,
                'postalCode' => $postalCode,
                'streetAddress' => $street,
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
                $organizerCreated,
                BroadwayDateTime::fromString($created)
            )
        );
    }

    /**
     * @test
     */
    public function it_handles_new_organizers_with_unique_website()
    {
        $uuidGenerator = new Version4Generator();
        $id = $uuidGenerator->generate();
        $created = '2015-01-20T13:25:21+01:00';

        $organizerCreated = new OrganizerCreatedWithUniqueWebsite(
            $id,
            Url::fromNative('http://www.stuk.be'),
            new Title('some representative title')
        );

        $jsonLD = new \stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/contexts/organizer';
        $jsonLD->url = 'http://www.stuk.be';
        $jsonLD->name['nl'] = 'some representative title';
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
                $organizerCreated,
                BroadwayDateTime::fromString($created)
            )
        );
    }

    /**
     * @test
     */
    public function it_handles_website_update()
    {
        $organizerId = '586f596d-7e43-4ab9-b062-04db9436fca4';
        $website = Url::fromNative('http://www.depot.be');

        $this->mockGet($organizerId, 'organizer.json');

        $domainMessage = $this->createDomainMessage(
            new WebsiteUpdated(
                $organizerId,
                $website
            )
        );

        $this->expectSave($organizerId, 'organizer_with_updated_website.json');

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_title_update()
    {
        $organizerId = '586f596d-7e43-4ab9-b062-04db9436fca4';
        $title = new Title('Het Depot');

        $this->mockGet($organizerId, 'organizer.json');

        $domainMessage = $this->createDomainMessage(
            new TitleUpdated(
                $organizerId,
                $title
            )
        );

        $this->expectSave($organizerId, 'organizer_with_updated_title.json');

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_title_translated()
    {
        $organizerId = '586f596d-7e43-4ab9-b062-04db9436fca4';
        $title = new Title('EssaiOrganisation');

        $this->mockGet($organizerId, 'organizer.json');

        $domainMessage = $this->createDomainMessage(
            new TitleTranslated(
                $organizerId,
                $title,
                new Language('fr')
            )
        );

        $this->expectSave($organizerId, 'organizer_with_translated_title.json');

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_translation_of_organizer_with_untranslated_name()
    {
        $organizerId = '586f596d-7e43-4ab9-b062-04db9436fca4';
        $title = new Title('EssaiOrganisation');

        $this->mockGet($organizerId, 'organizer_untranslated_name.json');

        $domainMessage = $this->createDomainMessage(
            new TitleTranslated(
                $organizerId,
                $title,
                new Language('fr')
            )
        );

        $this->expectSave($organizerId, 'organizer_with_translated_title.json');

        $this->projector->handle($domainMessage);
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

                $emails = $body->contactPoint->email;
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

                return empty($body->contactPoint->email);
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

                $emails = $body->contactPoint->email;
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

                $phones = $body->contactPoint->phone;
                $expectedPhones = [
                    '+32 3 260 96 10'
                ];

                return is_array($phones) && $phones == $expectedPhones;
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

                $phones = $body->contactPoint->phone;
                $expectedPhones = [
                    '+32 3 260 96 10',
                    '+32 3 062 69 01'
                ];

                return is_array($phones) && $phones == $expectedPhones;
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

                return empty($body->contactPoint->phone);
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
        $label = new Label('labelName', true);

        $this->mockGet($organizerId, 'organizer.json');

        $labelAdded = new LabelAdded($organizerId, $label);
        $domainMessage = $this->createDomainMessage($labelAdded);

        $this->expectSave($organizerId, 'organizer_with_one_label.json');

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_invisible_label_added()
    {
        $organizerId = '586f596d-7e43-4ab9-b062-04db9436fca4';
        $label = new Label('labelName', false);

        $this->mockGet($organizerId, 'organizer.json');

        $labelAdded = new LabelAdded($organizerId, $label);
        $domainMessage = $this->createDomainMessage($labelAdded);

        $this->expectSave($organizerId, 'organizer_with_one_label_invisible.json');

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     * @dataProvider labelRemovedDataProvider
     * @param Label $label
     * @param string $originalFile
     * @param string $finalFile
     */
    public function it_handles_label_removed(
        Label $label,
        $originalFile,
        $finalFile
    ) {
        $organizerId = '586f596d-7e43-4ab9-b062-04db9436fca4';

        $this->mockGet($organizerId, $originalFile);

        $labelRemoved = new LabelRemoved($organizerId, $label);
        $domainMessage = $this->createDomainMessage($labelRemoved);

        $this->expectSave($organizerId, $finalFile);

        $this->projector->handle($domainMessage);
    }

    /**
     * @return array
     */
    public function labelRemovedDataProvider()
    {
        return [
            [
                new Label('labelName'),
                'organizer_with_one_label.json',
                'organizer.json'
            ],
            [
                new Label('anotherLabel'),
                'organizer_with_two_labels.json',
                'organizer_with_one_label.json'
            ],
            [
                new Label('yetAnotherLabel'),
                'organizer_with_three_labels.json',
                'organizer_with_two_labels.json'
            ]
        ];
    }

    /**
     * @test
     */
    public function it_handles_invisible_label_removed()
    {
        $organizerId = '586f596d-7e43-4ab9-b062-04db9436fca4';
        $label = new Label('labelName', false);

        $this->mockGet($organizerId, 'organizer_with_one_label_invisible.json');

        $labelRemoved = new LabelRemoved($organizerId, $label);
        $domainMessage = $this->createDomainMessage($labelRemoved);

        $this->expectSave($organizerId, 'organizer.json');

        $this->projector->handle($domainMessage);
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
        $expectedOrganizerJson = file_get_contents(__DIR__ . '/Samples/' . $fileName);
        // The expected organizer json still has newline formatting.
        // The actual organizer json on the other hand has no newlines
        // because it was created by using the withBody method on JsonDocument.
        // By calling json_encode(json_decode(...)) the newlines are also removed
        // from the expected document.
        $expectedOrganizerJson = json_encode(json_decode($expectedOrganizerJson));

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with(new JsonDocument($organizerId, $expectedOrganizerJson));
    }

    /**
     * @param OrganizerEvent $organizerEvent
     * @return DomainMessage
     */
    private function createDomainMessage(OrganizerEvent $organizerEvent)
    {
        return new DomainMessage(
            $organizerEvent->getOrganizerId(),
            0,
            new Metadata(),
            $organizerEvent,
            BroadwayDateTime::now()
        );
    }
}
