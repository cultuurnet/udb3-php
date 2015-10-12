<?php

namespace CultuurNet\UDB3\Variations\ReadModel\JSONLD;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationCreated;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationDeleted;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\ReadModel\Search\Criteria;
use CultuurNet\UDB3\Variations\ReadModel\Search\RepositoryInterface as SearchRepositoryInterface;

class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

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
        IriGeneratorInterface $variationIriGenerator
    ) {
        $this->repository = $repository;
        $this->eventRepository = $eventRepository;
        $this->searchRepository = $searchRepository;
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

        if (!$eventDocument) {
            return;
        }

        $searchCriteria = new Criteria();
        $eventUrl = new Url($eventDocument->getBody()->{'@id'});
        $searchCriteria = $searchCriteria->withEventUrl($eventUrl);
        $variationIds = $this->searchRepository->getEventVariations($searchCriteria);

        foreach ($variationIds as $variationId) {
            $variationDocument = $this->repository->get($variationId);
            // use the up-to-date event json as a base
            $variationLD = $eventDocument->getBody();

            // overwrite the description that's already set in the variation
            $variationLD->description->nl = $variationDocument->getBody()->description->nl;

            // overwrite the event url with the variation url
            $variationLD->{'@id'} = $this->variationIriGenerator->iri($variationId);

            // add the original event to the list of similar entities
            $existingSameAsEntities = $variationDocument->getBody()->sameAs;
            $newSameAsEntities = array_unique(array_merge(
                $existingSameAsEntities,
                [$eventUrl]
            ));
            $variationLD->sameAs = $newSameAsEntities;

            $this->repository->save($variationDocument->withBody($variationLD));
        }
    }

    /**
     * @param EventVariationCreated $eventVariationCreated
     */
    public function applyEventVariationCreated(EventVariationCreated $eventVariationCreated)
    {
        // TODO: figure out how to get the event id without parsing it from the URL
        $eventUrlParts = explode('/', $eventVariationCreated->getEventUrl());
        $eventId = end($eventUrlParts);
        $eventDocument = $this->eventRepository->get($eventId);

        // use the up-to-date event json as a base
        $variationLD = $eventDocument->getBody();

        // overwrite the description that's already set in the variation
        $variationLD->description->nl = (string)$eventVariationCreated->getDescription();

        // overwrite the event url with the variation url
        $variationLD->{'@id'} = $this->variationIriGenerator->iri($eventVariationCreated->getId());

        // add the original event to the list of similar entities
        $existingSameAsEntities = $eventDocument->getBody()->sameAs;
        $newSameAsEntities = array_unique(array_merge(
            $existingSameAsEntities,
            [(string)$eventVariationCreated->getEventUrl()]
        ));
        $variationLD->sameAs = $newSameAsEntities;

        $variationDocument = new JsonDocument($eventVariationCreated->getId(), $variationLD);
        $this->repository->save($variationDocument->withBody($variationLD));
    }

    /**
     * @param EventVariationDeleted $eventVariationDeleted
     */
    public function applyEventVariationDeleted(EventVariationDeleted $eventVariationDeleted)
    {
        $this->repository->remove((string)$eventVariationDeleted->getId());
    }
}
