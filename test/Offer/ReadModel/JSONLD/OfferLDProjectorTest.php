<?php

namespace CultuurNet\UDB3\Offer\ReadModel\JSONLD;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\InMemoryDocumentRepository;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Item\Events\DescriptionTranslated;
use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelDeleted;
use CultuurNet\UDB3\Offer\Item\Events\TitleTranslated;
use CultuurNet\UDB3\Offer\Item\ReadModel\JSONLD\ItemLDProjector;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use ValueObjects\String\String;

class OfferLDProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryDocumentRepository
     */
    protected $documentRepository;

    /**
     * @var ItemLDProjector
     */
    protected $projector;

    /**
     * @var IriGeneratorInterface
     */
    private $iriGenerator;

    /**
     * @var OrganizerService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $organizerService;

    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName, 'CultuurNet\\UDB3\\Offer\\Item');
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->documentRepository = new InMemoryDocumentRepository();

        $this->organizerService = $this->getMock(
            OrganizerService::class,
            array(),
            array(),
            '',
            false
        );

        $this->iriGenerator = new CallableIriGenerator(
            function ($id) {
                return 'http://example.com/entity/' . $id;
            }
        );

        $this->projector = new ItemLDProjector(
            $this->documentRepository,
            $this->iriGenerator,
            $this->organizerService
        );
    }

    /**
     * @param object $event
     * @param string $entityId
     * @param Metadata|null $metadata
     * @param DateTime $dateTime
     * @return \stdClass
     */
    protected function project(
        $event,
        $entityId,
        Metadata $metadata = null,
        DateTime $dateTime = null
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

        return $this->getBody($entityId);
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
    public function it_projects_the_addition_of_a_label()
    {
        $labelAdded = new LabelAdded(
            'foo',
            new Label('label B')
        );

        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'labels' => ['label A']
            ])
        );

        $this->documentRepository->save($initialDocument);

        $body = $this->project($labelAdded, 'foo');

        $this->assertEquals(
            ['label A', 'label B'],
            $body->labels
        );
    }

    /**
     * @test
     */
    public function it_projects_the_removal_of_a_label()
    {
        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'labels' => ['label A', 'label B', 'label C']
            ])
        );

        $this->documentRepository->save($initialDocument);

        $labelDeleted = new LabelDeleted(
            'foo',
            new Label('label B')
        );

        $body = $this->project($labelDeleted, 'foo');

        $this->assertEquals(
            ['label A', 'label C'],
            $body->labels
        );
    }

    /**
     * @test
     */
    public function it_projects_the_addition_of_a_label_to_an_event_without_existing_labels()
    {
        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'bar' => 'stool'
            ])
        );

        $this->documentRepository->save($initialDocument);

        $labelAdded = new LabelAdded(
            'foo',
            new Label('label B')
        );

        $body = $this->project($labelAdded, 'foo');

        $expectedBody = new stdClass();
        $expectedBody->bar = 'stool';
        $expectedBody->labels = ['label B'];

        $this->assertEquals(
            $expectedBody,
            $body
        );

    }

    /**
     * @test
     */
    public function it_projects_the_translation_of_the_title()
    {
        $titleTranslated = new TitleTranslated(
            'foo',
            new Language('en'),
            new String('English title')
        );

        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'name' => [
                    'nl'=> 'Titel'
                ],
                'description' => [
                    'nl' => 'Omschrijving'
                ],
            ])
        );

        $this->documentRepository->save($initialDocument);

        $body = $this->project($titleTranslated, 'foo');

        $this->assertEquals(
            (object)[
                'name' => (object)[
                    'nl'=> 'Titel',
                    'en' => 'English title'
                ],
                'description' => (object)[
                    'nl' => 'Omschrijving'
                ],
            ],
            $body
        );
    }

    /**
     * @test
     */
    public function it_projects_the_translation_of_the_description()
    {
        $descriptionTranslated = new DescriptionTranslated(
            'foo',
            new Language('en'),
            new String('English description')
        );

        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'name' => [
                    'nl'=> 'Titel'
                ],
                'description' => [
                    'nl' => 'Omschrijving'
                ],
            ])
        );

        $this->documentRepository->save($initialDocument);

        $body = $this->project($descriptionTranslated, 'foo');

        $this->assertEquals(
            (object)[
                'name' => (object)[
                    'nl'=> 'Titel',
                ],
                'description' => (object)[
                    'nl' => 'Omschrijving',
                    'en' => 'English description',
                ],
            ],
            $body
        );
    }
}
