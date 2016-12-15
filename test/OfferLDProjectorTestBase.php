<?php

/**
 * @file
 * Contains CultuurNet\UDB3\OfferLDProjectorTestTrait.
 */

namespace CultuurNet\UDB3;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\InMemoryDocumentRepository;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Media\Properties\CopyrightHolder;
use CultuurNet\UDB3\Media\Properties\Description;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

/**
 * Base test  case class for testing common Offer JSON-LD projector
 * functionality.
 */
abstract class OfferLDProjectorTestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryDocumentRepository
     */
    protected $documentRepository;

    /**
     * @var EventListenerInterface
     */
    protected $projector;

    /**
     * @var string
     */
    protected $eventNamespace;

    /**
     * @var OrganizerService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $organizerService;

    public function __construct($name, array $data, $dataName, $eventNamespace)
    {
        parent::__construct($name, $data, $dataName);

        $this->eventNamespace = $eventNamespace;
    }

    /**
     * Get the namespaced classname of the event to create.
     * @param string $className
     *   Name of the class
     * @return string
     */
    private function getEventClass($className)
    {
        return $this->eventNamespace . '\\Events\\' . $className;
    }

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->documentRepository = new InMemoryDocumentRepository();

        $this->organizerService = $this->getMock(
            OrganizerService::class,
            array(),
            array(),
            '',
            false
        );
    }

    /**
     * @param object $event
     * @param string $entityId
     * @param Metadata|null $metadata
     * @param DateTime $dateTime
     * @param bool $returnBody
     * @return \stdClass
     */
    protected function project(
        $event,
        $entityId,
        Metadata $metadata = null,
        DateTime $dateTime = null,
        $returnBody = true
    ) {
        if (null === $metadata) {
            $metadata = new Metadata();
        }

        if (null === $dateTime) {
            $dateTime = DateTime::now();
        }

        $this->projector->handle(
            new DomainMessage(
                $entityId,
                1,
                $metadata,
                $event,
                $dateTime
            )
        );

        if ($returnBody) {
            return $this->getBody($entityId);
        }
    }

    /**
     * @param string $id
     * @return \stdClass
     */
    protected function getBody($id)
    {
        $document = $this->documentRepository->get($id);
        return $document->getBody();
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_booking_info()
    {
        $id = 'foo';
        $url = 'http://www.google.be';
        $urlLabel = 'Google';
        $phone = '045';
        $email = 'test@test.com';
        $availabilityStarts = '12';
        $availabilityEnds = '14';
        $name = 'Booking name';
        $description = 'booking description';
        $bookingInfo = new BookingInfo($url, $urlLabel, $phone, $email, $availabilityStarts, $availabilityEnds, $name, $description);
        $eventClass = $this->getEventClass('BookingInfoUpdated');
        $bookingInfoUpdated = new $eventClass($id, $bookingInfo);

        $initialDocument = new JsonDocument($id);

        $this->documentRepository->save($initialDocument);

        $expectedBody = (object)[
            'bookingInfo' => (object)[
                'phone' => $phone,
                'email' => $email,
                'url' => $url,
                'urlLabel' => $urlLabel,
                'name' => $name,
                'description' => $description,
                'availabilityStarts' => $availabilityStarts,
                'availabilityEnds' => $availabilityEnds
            ]
        ];

        $body = $this->project($bookingInfoUpdated, $id);

        $this->assertEquals($expectedBody, $body);
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_contact_point()
    {
        $id = 'foo';
        $phones = ['045', '046'];
        $emails = ['test@test.be', 'test@test2.be'];
        $urls = ['http://www.google.be', 'http://www.google2.be'];
        $contactPoint = new ContactPoint($phones, $emails, $urls);
        $eventClass = $this->getEventClass('ContactPointUpdated');
        $contactPointUpdated = new $eventClass($id, $contactPoint);

        $initialDocument = new JsonDocument($id);
        $this->documentRepository->save($initialDocument);

        $body = $this->project($contactPointUpdated, $id);

        $expectedBody = (object)[
            'contactPoint' => (object)[
                'phone' => $phones,
                'email' => $emails,
                'url' => $urls,
            ]
        ];

        $this->assertEquals(
            $expectedBody,
            $body
        );
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_description()
    {
        $description = 'description';
        $id = 'foo';
        $eventClass = $this->getEventClass('DescriptionUpdated');
        $descriptionUpdated = new $eventClass($id, $description);

        $initialDocument = new JsonDocument($id);
        $this->documentRepository->save($initialDocument);

        $expectedBody = (object)[
            'description' => (object)[
                'nl' => $description
            ]
        ];

        $body = $this->project($descriptionUpdated, $id);

        $this->assertEquals($expectedBody, $body);
    }

    /**
     * @test
     */
    public function it_projects_the_adding_of_an_image()
    {
        $id = 'foo';
        $imageId = UUID::fromNative('de305d54-75b4-431b-adb2-eb6b9e546014');
        $description = new Description('Some description.');
        $copyrightHolder = new CopyrightHolder('Dirk Dirkington');
        $type = new MIMEType('image/png');
        $location = Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png');

        $image = new Image($imageId, $type, $description, $copyrightHolder, $location);
        $eventClass = $this->getEventClass('ImageAdded');
        $imageAdded = new $eventClass($id, $image);

        $initialDocument = new JsonDocument($id);
        $this->documentRepository->save($initialDocument);

        $expectedBody = (object)[
            'image' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
            'mediaObject' => [
                (object)[
                    '@id' => 'http://example.com/entity/de305d54-75b4-431b-adb2-eb6b9e546014',
                    '@type' => 'schema:ImageObject',
                    'contentUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                    'thumbnailUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                    'description' => (string) $description,
                    'copyrightHolder' => (string) $copyrightHolder
                ]
            ]
        ];

        $body = $this->project($imageAdded, $id);
        $this->assertEquals($expectedBody, $body);
    }

    /**
     * @test
     */
    public function it_projects_the_editing_of_an_image()
    {
        $id = 'foo';
        $imageId = UUID::fromNative('de305d54-75b4-431b-adb2-eb6b9e546014');
        $description = String::fromNative('Some description.');
        $copyrightHolder = String::fromNative('Dirk Dirkington');
        $type = new MIMEType('image/png');
        $location = Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png');

        $mediaObject = MediaObject::create($imageId, $type, $description, $copyrightHolder, $location);
        $eventClass = $this->getEventClass('ImageUpdated');
        $imageUpdated = new $eventClass($id, $imageId, $description, $copyrightHolder);

        $initialDocument = new JsonDocument(
            $id,
            json_encode([
                'mediaObject' => [
                    [
                        '@id' => 'http://example.com/entity/de305d54-75b4-431b-adb2-eb6b9e546014',
                        '@type' => 'schema:ImageObject',
                        'contentUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                        'thumbnailUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                        'description' => 'olddescription',
                        'copyrightHolder' => 'oldcopyrightHolder'
                    ]
                ]
            ])
        );
        $this->documentRepository->save($initialDocument);

        $expectedBody = (object)[
            'mediaObject' => [
                (object)[
                    '@id' => 'http://example.com/entity/de305d54-75b4-431b-adb2-eb6b9e546014',
                    '@type' => 'schema:ImageObject',
                    'contentUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                    'thumbnailUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                    'description' => (string) $description,
                    'copyrightHolder' => (string) $copyrightHolder
                ]
            ]
        ];

        $body = $this->project($imageUpdated, $id);

        $this->assertEquals($expectedBody, $body);
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_age_range()
    {
        $id = 'foo';
        $eventClass = $this->getEventClass('TypicalAgeRangeUpdated');
        $typicalAgeRangeUpdated = new $eventClass($id, '-18');

        $initialDocument = new JsonDocument(
            $id,
            json_encode([
                'typicalAgeRange' => '12-14'
            ])
        );
        $this->documentRepository->save($initialDocument);

        $expectedBody = (object)[
            'typicalAgeRange' => '-18'
        ];

        $body = $this->project($typicalAgeRangeUpdated, $id);

        $this->assertEquals($expectedBody, $body);
    }

    /**
     * @test
     */
    public function it_projects_the_deleting_of_age_range()
    {
        $id = 'foo';
        $eventClass = $this->getEventClass('TypicalAgeRangeDeleted');
        $typicalAgeRangeDeleted = new $eventClass($id);

        $initialDocument = new JsonDocument(
            $id,
            json_encode([
                'typicalAgeRange' => '-18'
            ])
        );
        $this->documentRepository->save($initialDocument);

        $body = $this->project($typicalAgeRangeDeleted, $id);

        $this->assertEquals(new \stdClass(), $body);
    }
}
