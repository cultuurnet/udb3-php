<?php

namespace CultuurNet\UDB3\Variations\ReadModel\JSONLD;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationCreated;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationDeleted;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use ValueObjects\Identity\UUID;
use CultuurNet\UDB3\Variations\ReadModel\Search\RepositoryInterface as SearchRepositoryInterface;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->repository = $this->getMock(
            DocumentRepositoryInterface::class
        );

        $this->eventRepository = $this->getMock(
            DocumentRepositoryInterface::class
        );

        $this->searchRepository = $this->getMock(
            SearchRepositoryInterface::class
        );

        $this->eventIriGenerator = $this->getMock(
            IriGeneratorInterface::class
        );

        $this->variationIriGenerator = $this->getMock(
            IriGeneratorInterface::class
        );

        $this->projector = new Projector(
            $this->repository,
            $this->eventRepository,
            $this->searchRepository,
            $this->variationIriGenerator
        );
    }

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var Projector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $projector;

    /**
     * @var IriGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventIriGenerator;

    /**
     * @var IriGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $variationIriGenerator;

    /**
     * @var SearchRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchRepository;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventRepository;

    /**
     * @test
     */
    public function it_updates_the_variation_description_when_edited()
    {
        $variationId = new Id(UUID::generateAsString());
        $description = new Description('This is a new description');
        $descriptionEdited = new DescriptionEdited($variationId, $description);

        $variation = new JsonDocument(
            (string) $variationId,
            json_encode([
                'description' => [
                    'nl' => 'The variation description'
                ]
            ])
        );

        $updatedVariation = new JsonDocument(
            (string) $variationId,
            json_encode([
                'description' => [
                    'nl' => 'This is a new description'
                ]
            ])
        );

        $this->repository
            ->expects($this->once())
            ->method('get')
            ->with((string) $variationId)
            ->willReturn($variation);

        $this->repository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($updatedVariation) {
                    return $updatedVariation == $jsonDocument;
                }
            ));

        $this->projector->applyDescriptionEdited($descriptionEdited);
    }

    /**
     * @test
     */
    public function it_updates_variations_when_the_original_event_changes()
    {
        $eventId = 'some-event-id';
        $eventUrl = 'http://acme.org/event/' . $eventId;

        $variationId = 'a-variation-id';
        $variationUrl = 'http://acme.org/variation/' . $variationId;
        $this->variationIriGenerator
            ->expects($this->atLeastOnce())
            ->method('iri')
            ->with($variationId)
            ->willReturn($variationUrl);

        $event = new JsonDocument(
            'some-event-id',
            json_encode([
                '@id' => $eventUrl,
                'description' => [
                    'nl' => 'Original event description',
                    'fr' => 'Le french translation'
                ],
                'sameAs' => []
            ])
        );

        $variation = new JsonDocument(
            'a-variation-id',
            json_encode([
                '@id' => $variationUrl,
                'description' => [
                    'nl' => 'The variation description'
                ],
                'sameAs' => [$eventUrl]
            ])
        );

        $expectedVariation = new JsonDocument(
            'a-variation-id',
            json_encode([
                '@id' => $variationUrl,
                'description' => [
                    'nl' => 'The variation description',
                    'fr' => 'Le french translation'
                ],
                'sameAs' => [$eventUrl]
            ])
        );

        $this->eventRepository->expects($this->once())
            ->method('get')
            ->willReturn($event);

        $this->searchRepository->expects($this->once())
            ->method('getEventVariations')
            ->willReturn([$variationId]);

        $this->repository
            ->expects($this->at(0))
            ->method('get')
            ->with('a-variation-id')
            ->willReturn($variation);
        $this->repository
            ->expects($this->at(1))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedVariation) {
                    return $expectedVariation == $jsonDocument;
                }
            ));

        $eventProjectedEvent = new EventProjectedToJSONLD($eventId);
        $this->projector->applyEventProjectedToJSONLD($eventProjectedEvent);
    }

    /**
     * @test
     */
    public function it_creates_variations()
    {
        $eventId = 'DEF99055-FE91-4039-B96C-1238529045C5';
        $eventUrl = 'http://acme.org/event/' . $eventId;

        $variationId = 'BAE91055-FE91-4039-B96C-29F5661045C5';
        $variationUrl = 'http://acme.org/variation/' . $variationId;
        $this->variationIriGenerator
            ->expects($this->atLeastOnce())
            ->method('iri')
            ->with($variationId)
            ->willReturn($variationUrl);

        $event = new JsonDocument(
            'DEF99055-FE91-4039-B96C-1238529045C5',
            json_encode([
                '@id' => $eventUrl,
                'description' => [
                    'nl' => 'Original event description',
                    'fr' => 'Le french translation'
                ],
                'sameAs' => []
            ])
        );

        $expectedVariation = new JsonDocument(
            'BAE91055-FE91-4039-B96C-29F5661045C5',
            json_encode([
                '@id' => $variationUrl,
                'description' => [
                    'nl' => 'The variation description',
                    'fr' => 'Le french translation'
                ],
                'sameAs' => [$eventUrl]
            ])
        );

        $this->eventRepository->expects($this->once())
            ->method('get')
            ->with('DEF99055-FE91-4039-B96C-1238529045C5')
            ->willReturn($event);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedVariation) {
                    return $expectedVariation == $jsonDocument;
                }
            ));

        $variationCreatedEvent = new EventVariationCreated(
            new Id($variationId),
            new Url($eventUrl),
            new OwnerId('this-is-a-owner-id'),
            new Purpose('personal'),
            new Description('The variation description')
        );
        $this->projector->applyEventVariationCreated($variationCreatedEvent);
    }

    /**
     * @test
     */
    public function it_removes_the_jsonld_document_when_a_variation_is_removed()
    {
        $eventVariationDeleted = new EventVariationDeleted(
            new Id('F9D326AD-B8E5-4F0C-AB23-7B6426133D30')
        );

        $this->repository->expects($this->once())
            ->method('remove')
            ->with('F9D326AD-B8E5-4F0C-AB23-7B6426133D30');

        $this->projector->handle(
            DomainMessage::recordNow(
                '',
                2,
                new Metadata(),
                $eventVariationDeleted
            )
        );
    }
}
