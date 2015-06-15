<?php

namespace CultuurNet\UDB3\Variations\ReadModel\JSONLD;

use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationDeleted;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\ReadModel\Search\Criteria;
use CultuurNet\UDB3\Variations\ReadModel\Search\RepositoryInterface as SearchRepositoryInterface;

class Projector implements ProjectorInterface
{
    /**
     * @var DocumentRepositoryInterface
     */
    protected $repository;

    /**
     * @var DocumentRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var SearchRepositoryInterface
     */
    protected $searchRepository;

    /**
     * @var IriGeneratorInterface
     */
    protected $eventIriGenerator;

    /**
     * @var IriGeneratorInterface
     */
    protected $variationIriGenerator;

    public function __construct(
        DocumentRepositoryInterface $repository,
        DocumentRepositoryInterface $eventRepository,
        SearchRepositoryInterface $searchRepository,
        IriGeneratorInterface $eventIriGenerator,
        IriGeneratorInterface $variationIriGenerator
    ) {
        $this->repository = $repository;
        $this->eventRepository = $eventRepository;
        $this->searchRepository = $searchRepository;
        $this->eventIriGenerator = $eventIriGenerator;
        $this->variationIriGenerator = $variationIriGenerator;
    }

    public function applyDescriptionEdited(DescriptionEdited $descriptionEdited)
    {
        $variation = $this->repository->get($descriptionEdited->getId());
        $variationLD = $variation->getBody();
        $language = 'nl';

        $variationLD->description->$language = (string) $descriptionEdited->getDescription();
        $this->repository->save($variation->withBody($variationLD));

    }

    /**
     * @param EventProjectedToJSONLD $eventProjectedToJSONLD
     */
    public function applyEventProjectedToJSONLD(EventProjectedToJSONLD $eventProjectedToJSONLD)
    {
        $eventId = $eventProjectedToJSONLD->getEventId();
        /** @var JsonDocument $eventDocument */
        $eventDocument = $this->eventRepository->get($eventId);

        $searchCriteria = new Criteria();
        $eventUrl = new Url($this->eventIriGenerator->iri($eventId));
        $searchCriteria->withEventUrl($eventUrl);
        /** @var JsonDocument[] $variations */
        $variations = $this->searchRepository->getEventVariations($searchCriteria);

        foreach ($variations as $variation) {
            // use the up-to-date event json as a base
            $variationLD = $eventDocument->getBody();

            // overwrite the description that's already set in the variation
            $variationLD->description->nl = $variation->getBody()->description->nl;

            // overwrite the event url with the variation url
            $variationLD->{'@id'} = $this->variationIriGenerator->iri(
                $variation->getId()
            );

            // add the original event to the list of similar entities
            $existingSameAsEntities = $variation->getBody()->sameAs;
            $newSameAsEntities = array_unique(array_merge(
                $existingSameAsEntities,
                [$eventUrl]
            ));
            $variationLD->sameAs = $newSameAsEntities;

            $this->repository->save($variation->withBody($variationLD));
        }
    }

    /**
     * @param EventVariationDeleted $eventVariationDeleted
     */
    public function applyEventVariationDeleted(EventVariationDeleted $eventVariationDeleted)
    {
        // TODO: Implement applyEventVariationDeleted() method.
    }
}
