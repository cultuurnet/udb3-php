<?php

namespace CultuurNet\UDB3\Variations\ReadModel\JSONLD;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Offer\Events\AbstractEventWithIri;
use CultuurNet\UDB3\Offer\OfferReadingServiceInterface;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationCreated;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationDeleted;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\ReadModel\Search\RepositoryInterface as SearchRepositoryInterface;
use ValueObjects\Identity\UUID;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
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
     * @var OfferReadingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $offerReadingService;

    protected function setUp()
    {
        $this->repository = $this->createMock(
            DocumentRepositoryInterface::class
        );

        $this->offerReadingService = $this->createMock(
            OfferReadingServiceInterface::class
        );

        $this->searchRepository = $this->createMock(
            SearchRepositoryInterface::class
        );

        $this->eventIriGenerator = $this->createMock(
            IriGeneratorInterface::class
        );

        $this->variationIriGenerator = $this->createMock(
            IriGeneratorInterface::class
        );

        $this->projector = new Projector(
            $this->repository,
            $this->offerReadingService,
            $this->searchRepository,
            $this->variationIriGenerator
        );
    }

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
     * @dataProvider offerProjectedToJSONLDDataProvider
     *
     * @param string $id
     * @param string $iri
     * @param AbstractEventWithIri $offerProjectedToJSONLDEvent
     */
    public function it_updates_variations_when_the_original_offer_changes(
        $id,
        $iri,
        AbstractEventWithIri $offerProjectedToJSONLDEvent
    ) {
        $variationId = 'a-variation-id';
        $variationUrl = 'http://acme.org/variation/' . $variationId;

        $this->variationIriGenerator
            ->expects($this->atLeastOnce())
            ->method('iri')
            ->with($variationId)
            ->willReturn($variationUrl);

        $offer = new JsonDocument(
            $id,
            json_encode([
                '@id' => $iri,
                'description' => [
                    'nl' => 'Original offer description',
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
                'sameAs' => [$iri]
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
                'sameAs' => [$iri]
            ])
        );

        $this->offerReadingService->expects($this->once())
            ->method('load')
            ->with($iri)
            ->willReturn($offer);

        $this->searchRepository->expects($this->once())
            ->method('getOfferVariations')
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

        $this->projector->handle(
            DomainMessage::recordNow(
                $id,
                0,
                new Metadata(),
                $offerProjectedToJSONLDEvent
            )
        );
    }

    /**
     * @test
     * @dataProvider offerProjectedToJSONLDDataProvider
     *
     * @param string $id
     * @param string $iri
     * @param AbstractEventWithIri $offerProjectedToJSONLDEvent
     */
    public function it_ignores_an_update_to_an_offer_if_it_can_not_load_the_offer(
        $id,
        $iri,
        AbstractEventWithIri $offerProjectedToJSONLDEvent
    ) {
        $this->offerReadingService->expects($this->once())
            ->method('load')
            ->with($iri)
            ->willReturn(false);

        $this->searchRepository->expects($this->never())
            ->method('getOfferVariations');

        $this->projector->handle(
            DomainMessage::recordNow(
                $id,
                0,
                new Metadata(),
                $offerProjectedToJSONLDEvent
            )
        );
    }

    /**
     * @return array
     */
    public function offerProjectedToJSONLDDataProvider()
    {
        return [
            [
                'some-event-id',
                'http://acme.org/event/some-event-id',
                new EventProjectedToJSONLD('some-event-id', 'http://acme.org/event/some-event-id'),
            ],
            [
                'some-place-id',
                'http://acme.org/event/some-place-id',
                new PlaceProjectedToJSONLD('some-place-id', 'http://acme.org/event/some-place-id'),
            ],
        ];
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

        $this->offerReadingService->expects($this->once())
            ->method('load')
            ->with($eventUrl)
            ->willReturn($event);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedVariation) {
                    return $expectedVariation == $jsonDocument;
                }
            ));

        $variationCreatedEvent = new OfferVariationCreated(
            new Id($variationId),
            new Url($eventUrl),
            new OwnerId('this-is-a-owner-id'),
            new Purpose('personal'),
            new Description('The variation description'),
            OfferType::EVENT()
        );
        $this->projector->applyOfferVariationCreated($variationCreatedEvent);
    }

    /**
     * @test
     */
    public function it_removes_the_jsonld_document_when_a_variation_is_removed()
    {
        $eventVariationDeleted = new OfferVariationDeleted(
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
