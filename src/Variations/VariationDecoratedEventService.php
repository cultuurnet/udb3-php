<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations;

use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\ReadModel\Search\Criteria;
use CultuurNet\UDB3\Variations\ReadModel\Search\RepositoryInterface;

class VariationDecoratedEventService implements EventServiceInterface
{
    /**
     * @var EventServiceInterface
     */
    private $decoratedEventService;

    /**
     * @var RepositoryInterface
     */
    private $searchRepository;

    /**
     * @var Criteria
     */
    private $baseCriteria;

    /**
     * @var DocumentRepositoryInterface
     */
    private $variationJsonLdRepository;

    /**
     * @var IriGeneratorInterface
     */
    private $eventIriGenerator;

    /**
     * @param EventServiceInterface $decoratedEventService
     * @param RepositoryInterface $searchRepository
     * @param Criteria $baseCriteria
     * @param DocumentRepositoryInterface $variationJsonLdRepository
     * @param IriGeneratorInterface $eventIriGenerator
     */
    public function __construct(
        EventServiceInterface $decoratedEventService,
        RepositoryInterface $searchRepository,
        Criteria $baseCriteria,
        DocumentRepositoryInterface $variationJsonLdRepository,
        IriGeneratorInterface $eventIriGenerator
    ) {
        $this->decoratedEventService = $decoratedEventService;
        $this->searchRepository = $searchRepository;
        $this->baseCriteria = $baseCriteria;
        $this->variationJsonLdRepository = $variationJsonLdRepository;
        $this->eventIriGenerator = $eventIriGenerator;
    }

    /**
     * @inheritdoc
     */
    public function getEvent($id)
    {
        try {
            $url = $this->eventIriGenerator->iri($id);

            $criteria = $this->baseCriteria->withEventUrl(
                new Url($url)
            );

            $variationIds = $this->searchRepository->getOfferVariations(
                $criteria
            );

            if (count($variationIds) > 0) {
                $variationId = reset($variationIds);

                $document = $this->variationJsonLdRepository->get($variationId);

                if ($document) {
                    return $document->getRawBody();
                }
            }
        } catch (DocumentGoneException $e) {
            // Document was gone. This is a situation that might occur
            // at a moment where the read models are not entirely consistent
            // yet. We just ignore the exception and fall back to the regular
            // event info.
        }

        return $this->decoratedEventService->getEvent($id);
    }

    /**
     * @inheritdoc
     */
    public function eventsOrganizedByOrganizer($organizerId)
    {
        return $this->decoratedEventService->eventsOrganizedByOrganizer($organizerId);
    }

    /**
     * @inheritdoc
     */
    public function eventsLocatedAtPlace($placeId)
    {
        return $this->decoratedEventService->eventsLocatedAtPlace($placeId);
    }
}
