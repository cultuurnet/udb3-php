<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations;

use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\InMemoryDocumentRepository;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\ReadModel\Search\Criteria;
use CultuurNet\UDB3\Variations\ReadModel\Search\RepositoryInterface;
use PHPUnit_Framework_TestCase;

class VariationDecoratedEventServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratedEventService;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $search;

    /**
     * @var Criteria
     */
    private $criteria;

    /**
     * @var DocumentRepositoryInterface
     */
    private $variationsJsonLdRepository;

    /**
     * @var IriGeneratorInterface
     */
    private $eventIriGenerator;

    /**
     * @var VariationDecoratedEventService
     */
    private $service;

    public function setUp()
    {
        $this->decoratedEventService = $this->getMock(EventServiceInterface::class);
        $this->search = $this->getMock(RepositoryInterface::class);
        $this->criteria = (new Criteria())->withPurpose(
            new Purpose('personal')
        );
        $this->variationsJsonLdRepository = new InMemoryDocumentRepository();
        $this->eventIriGenerator = new CallableIriGenerator(
            function ($item) {
                return 'http://example.com/event/' . $item;
            }
        );

        $this->service = new VariationDecoratedEventService(
            $this->decoratedEventService,
            $this->search,
            $this->criteria,
            $this->variationsJsonLdRepository,
            $this->eventIriGenerator
        );
    }

    /**
     * @test
     */
    public function it_uses_a_variation_if_one_exists()
    {
        $eventId = '937E901C-2E15-4F28-92EE-CD0AAFF44DB0';
        $variationId = 'D83B8FBC-9583-4F09-AE3F-C60393226D24';
        $variationJsonLD = $this->variationJsonLD(
            $variationId,
            $eventId
        );

        $this->variationsJsonLdRepository->save(
            new JsonDocument(
                $variationId,
                $variationJsonLD
            )
        );

        $expectedCriteria = $this->criteria->withEventUrl(
            new Url('http://example.com/event/937E901C-2E15-4F28-92EE-CD0AAFF44DB0')
        );

        $this->search->expects($this->once())
            ->method('getEventVariations')
            ->with($expectedCriteria)
            ->willReturn([$variationId]);

        $this->decoratedEventService->expects($this->never())
            ->method('getEvent');

        $jsonLD = $this->service->getEvent($eventId);

        $this->assertEquals(
            $variationJsonLD,
            $jsonLD
        );
    }

    /**
     * @test
     */
    public function it_falls_back_to_real_event_service_if_no_variation_is_found()
    {
        $eventId = '937E901C-2E15-4F28-92EE-CD0AAFF44DB0';

        $expectedCriteria = $this->criteria->withEventUrl(
            new Url('http://example.com/event/937E901C-2E15-4F28-92EE-CD0AAFF44DB0')
        );

        $eventJsonLD = $this->eventJsonLD($eventId);

        $this->search->expects($this->once())
            ->method('getEventVariations')
            ->with($expectedCriteria)
            ->willReturn([]);

        $this->decoratedEventService->expects($this->once())
            ->method('getEvent')
            ->with($eventId)
            ->willReturn(
                $eventJsonLD
            );

        $jsonLD = $this->service->getEvent($eventId);

        $this->assertEquals(
            $eventJsonLD,
            $jsonLD
        );
    }

    /**
     * @test
     */
    public function it_falls_back_to_the_real_event_service_if_the_variation_was_not_found()
    {
        $eventId = '937E901C-2E15-4F28-92EE-CD0AAFF44DB0';
        $variationId = 'D83B8FBC-9583-4F09-AE3F-C60393226D24';

        $eventJsonLD = $this->eventJsonLD($eventId);

        $expectedCriteria = $this->criteria->withEventUrl(
            new Url('http://example.com/event/937E901C-2E15-4F28-92EE-CD0AAFF44DB0')
        );

        $this->search->expects($this->once())
            ->method('getEventVariations')
            ->with($expectedCriteria)
            ->willReturn([$variationId]);

        $this->decoratedEventService->expects($this->once())
            ->method('getEvent')
            ->with($eventId)
            ->willReturn(
                $eventJsonLD
            );

        $jsonLD = $this->service->getEvent($eventId);

        $this->assertEquals(
            $eventJsonLD,
            $jsonLD
        );
    }

    /**
     * @test
     */
    public function it_falls_back_to_the_real_event_service_if_the_variation_was_removed()
    {
        $eventId = '937E901C-2E15-4F28-92EE-CD0AAFF44DB0';
        $variationId = 'D83B8FBC-9583-4F09-AE3F-C60393226D24';
        $variationJsonLD = $this->variationJsonLD(
            $variationId,
            $eventId
        );
        $eventJsonLD = $this->eventJsonLD($eventId);

        $this->variationsJsonLdRepository->save(
            new JsonDocument(
                $variationId,
                $variationJsonLD
            )
        );
        $this->variationsJsonLdRepository->remove($variationId);

        $expectedCriteria = $this->criteria->withEventUrl(
            new Url('http://example.com/event/937E901C-2E15-4F28-92EE-CD0AAFF44DB0')
        );

        $this->search->expects($this->once())
            ->method('getEventVariations')
            ->with($expectedCriteria)
            ->willReturn([$variationId]);

        $this->decoratedEventService->expects($this->once())
            ->method('getEvent')
            ->with($eventId)
            ->willReturn(
                $eventJsonLD
            );

        $jsonLD = $this->service->getEvent($eventId);

        $this->assertEquals(
            $eventJsonLD,
            $jsonLD
        );
    }

    private function eventJsonLD($eventId)
    {
        return json_encode(
            [
                '@id' => 'http://example.com/event/' . $eventId
            ]
        );
    }

    private function variationJsonLD($variationId, $eventId)
    {
        return json_encode(
            [
                '@id' => 'http://example.com/variations/' . $variationId,
                'sameAs' => [
                    'http://example.com/event/' . $eventId,
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_passes_through_events_organized_by_organizer_from_real_service()
    {
        $organizerId = '733BEE0B-6898-4290-8607-62EDB62F1BF5';

        $events = [
            'D68DC9B1-B864-4F44-BB40-D6BCDC288213',
            '2A971CDD-7D17-40FD-BC6D-5331E73BDEAB',
        ];

        $this->decoratedEventService->expects($this->once())
            ->method('eventsOrganizedByOrganizer')
            ->with($organizerId)
            ->willReturn($events);

        $actualEvents = $this->service->eventsOrganizedByOrganizer($organizerId);

        $this->assertEquals(
            $events,
            $actualEvents
        );
    }

    /**
     * @test
     */
    public function it_passes_through_events_located_at_place_from_real_service()
    {
        $placeId = '733BEE0B-6898-4290-8607-62EDB62F1BF5';

        $events = [
            'D68DC9B1-B864-4F44-BB40-D6BCDC288213',
            '2A971CDD-7D17-40FD-BC6D-5331E73BDEAB',
        ];

        $this->decoratedEventService->expects($this->once())
            ->method('eventsLocatedAtPlace')
            ->with($placeId)
            ->willReturn($events);

        $actualEvents = $this->service->eventsLocatedAtPlace($placeId);

        $this->assertEquals(
            $events,
            $actualEvents
        );
    }
}
