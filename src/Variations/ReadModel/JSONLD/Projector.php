<?php

namespace CultuurNet\UDB3\Variations\ReadModel\JSONLD;

use CultuurNet\UDB3\Event\EventProjectedToJSONLD;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationDeleted;

class Projector implements ProjectorInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
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
        // TODO: Implement applyEventProjectedToJSONLD() method.
    }

    /**
     * @param EventVariationDeleted $eventVariationDeleted
     */
    public function applyEventVariationDeleted(EventVariationDeleted $eventVariationDeleted)
    {
        // TODO: Implement applyEventVariationDeleted() method.
    }
}
