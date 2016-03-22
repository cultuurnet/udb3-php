<?php

namespace CultuurNet\UDB3\Variations\ReadModel\JSONLD;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractEventWithIri;
use CultuurNet\UDB3\Offer\OfferReadingServiceInterface;
use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationCreated;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationDeleted;
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
     * @var OfferReadingServiceInterface
     */
    protected $offerReadingService;

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

    /**
     * @param DocumentRepositoryInterface $repository
     * @param OfferReadingServiceInterface $offerReadingService
     * @param SearchRepositoryInterface $searchRepository
     * @param IriGeneratorInterface $variationIriGenerator
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        OfferReadingServiceInterface $offerReadingService,
        SearchRepositoryInterface $searchRepository,
        IriGeneratorInterface $variationIriGenerator
    ) {
        $this->repository = $repository;
        $this->offerReadingService = $offerReadingService;
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
        $this->applyOfferProjectedToJSONLD($eventProjectedToJSONLD);
    }

    /**
     * @param PlaceProjectedToJSONLD $placeProjectedToJSONLD
     */
    public function applyPlaceProjectedToJSONLD(PlaceProjectedToJSONLD $placeProjectedToJSONLD)
    {
        $this->applyOfferProjectedToJSONLD($placeProjectedToJSONLD);
    }

    /**
     * @param AbstractEventWithIri $event
     */
    private function applyOfferProjectedToJSONLD(AbstractEventWithIri $event)
    {
        $iri = $event->getIri();
        $document = $this->offerReadingService->load($iri);

        if (!$document) {
            return;
        }

        $searchCriteria = new Criteria();
        $eventUrl = new Url($iri);
        $searchCriteria = $searchCriteria->withOriginUrl($eventUrl);
        $variationIds = $this->searchRepository->getOfferVariations($searchCriteria);

        foreach ($variationIds as $variationId) {
            $variationDocument = $this->repository->get($variationId);
            // use the up-to-date event json as a base
            $variationLD = $document->getBody();

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
     * @param OfferVariationCreated $eventVariationCreated
     */
    public function applyOfferVariationCreated(OfferVariationCreated $eventVariationCreated)
    {
        $offerDocument = $this->offerReadingService->load(
            $eventVariationCreated->getOriginUrl()
        );

        // use the up-to-date event json as a base
        $variationLD = $offerDocument->getBody();

        // overwrite the description that's already set in the variation
        $variationLD->description->nl = (string)$eventVariationCreated->getDescription();

        // overwrite the offer url with the variation url
        $variationLD->{'@id'} = $this->variationIriGenerator->iri($eventVariationCreated->getId());

        // add the original event to the list of similar entities
        $existingSameAsEntities = $offerDocument->getBody()->sameAs;
        $newSameAsEntities = array_unique(array_merge(
            $existingSameAsEntities,
            [(string)$eventVariationCreated->getOriginUrl()]
        ));
        $variationLD->sameAs = $newSameAsEntities;

        $variationDocument = new JsonDocument($eventVariationCreated->getId(), $variationLD);
        $this->repository->save($variationDocument->withBody($variationLD));
    }

    /**
     * @param OfferVariationDeleted $eventVariationDeleted
     */
    public function applyOfferVariationDeleted(OfferVariationDeleted $eventVariationDeleted)
    {
        $this->repository->remove((string)$eventVariationDeleted->getId());
    }
}
