<?php

namespace CultuurNet\UDB3\Offer\ReadModel\History;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Description;
use CultuurNet\UDB3\Event\ReadModel\InMemoryDocumentRepository;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Item\Events\DescriptionTranslated;
use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelRemoved;
use CultuurNet\UDB3\Offer\Item\Events\TitleTranslated;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Offer\Item\ReadModel\History\ItemHistoryProjector;
use CultuurNet\UDB3\Title;

class OfferHistoryProjectorTest extends \PHPUnit_Framework_TestCase
{
    const EVENT_ID_1 = 'a0ee7b1c-a9c1-4da1-af7e-d15496014656';
    const EVENT_ID_2 = 'a2d50a8d-5b83-4c8b-84e6-e9c0bacbb1a3';

    const CDBXML_NAMESPACE = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';

    /**
     * @var InMemoryDocumentRepository
     */
    protected $documentRepository;

    /**
     * @var ItemHistoryProjector
     */
    protected $projector;

    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->documentRepository = new InMemoryDocumentRepository();

        $this->projector = new ItemHistoryProjector(
            $this->documentRepository
        );
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
    public function it_logs_the_addition_of_a_label()
    {
        $labelAdded = new LabelAdded(
            self::EVENT_ID_1,
            new Label('foo')
        );

        $initialDocument = new JsonDocument(
            self::EVENT_ID_1,
            json_encode([
            ])
        );

        $this->documentRepository->save($initialDocument);

        $taggedDate = '2015-03-27T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $labelAdded->getItemId(),
            2,
            new Metadata(['user_nick' => 'Jan Janssen']),
            $labelAdded,
            DateTime::fromString($taggedDate)
        );

        $this->projector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_1,
            [
                (object)[
                    'date' => '2015-03-27T10:17:19+02:00',
                    'author' => 'Jan Janssen',
                    'description' => "Label 'foo' toegepast",
                ],
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_the_removal_of_a_label()
    {
        $labelRemoved = new LabelRemoved(
            self::EVENT_ID_1,
            new Label('foo')
        );

        $initialDocument = new JsonDocument(
            self::EVENT_ID_1,
            json_encode([
            ])
        );

        $this->documentRepository->save($initialDocument);

        $tagErasedDate = '2015-03-27T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $labelRemoved->getItemId(),
            2,
            new Metadata(['user_nick' => 'Jan Janssen']),
            $labelRemoved,
            DateTime::fromString($tagErasedDate)
        );

        $this->projector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_1,
            [
                (object)[
                    'date' => '2015-03-27T10:17:19+02:00',
                    'author' => 'Jan Janssen',
                    'description' => "Label 'foo' verwijderd",
                ],
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_the_translation_of_a_title()
    {
        $titleTranslated = new TitleTranslated(
            self::EVENT_ID_1,
            new Language('en'),
            new Title('English title')
        );

        $initialDocument = new JsonDocument(
            self::EVENT_ID_1,
            json_encode([
            ])
        );

        $this->documentRepository->save($initialDocument);

        $taggedDate = '2015-03-27T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $titleTranslated->getItemId(),
            2,
            new Metadata(['user_nick' => 'Jan Janssen']),
            $titleTranslated,
            DateTime::fromString($taggedDate)
        );

        $this->projector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_1,
            [
                (object)[
                    'date' => '2015-03-27T10:17:19+02:00',
                    'author' => 'Jan Janssen',
                    'description' => "Titel vertaald (en)",
                ],
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_the_translation_of_a_description()
    {
        $descriptionTranslated = new DescriptionTranslated(
            self::EVENT_ID_1,
            new Language('en'),
            new Description('English description')
        );

        $initialDocument = new JsonDocument(
            self::EVENT_ID_1,
            json_encode([
            ])
        );

        $this->documentRepository->save($initialDocument);

        $taggedDate = '2015-03-27T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $descriptionTranslated->getItemId(),
            2,
            new Metadata(['user_nick' => 'Jan Janssen']),
            $descriptionTranslated,
            DateTime::fromString($taggedDate)
        );

        $this->projector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_1,
            [
                (object)[
                    'date' => '2015-03-27T10:17:19+02:00',
                    'author' => 'Jan Janssen',
                    'description' => "Beschrijving vertaald (en)",
                ],
            ]
        );
    }

    /**
     * @param string $eventId
     * @param array $history
     */
    protected function assertHistoryOfEvent($eventId, $history)
    {
        /** @var JsonDocument $document */
        $document = $this->documentRepository->get($eventId);

        $this->assertEquals(
            $history,
            $document->getBody()
        );
    }

    /**
     * @param string $userNick
     * @param string $consumerName
     * @return Metadata
     */
    protected function entryApiMetadata($userNick, $consumerName)
    {
        $values = [
            'user_nick' => $userNick,
            'consumer' => [
                'name' => $consumerName,
            ],
        ];

        return new Metadata($values);
    }
}
