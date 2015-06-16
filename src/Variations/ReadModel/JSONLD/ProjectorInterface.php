<?php

namespace CultuurNet\UDB3\Variations\ReadModel\JSONLD;

use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationDeleted;
use CultuurNet\UDB3\Variations\ReadModel\Search\RepositoryInterface as SearchRepositoryInterface;

interface ProjectorInterface
{
    /**
     * @param DocumentRepositoryInterface $repository
     * @param DocumentRepositoryInterface $eventRepository
     * @param SearchRepositoryInterface $searchRepository
     * @param IriGeneratorInterface $variationIriGenerator
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        DocumentRepositoryInterface $eventRepository,
        SearchRepositoryInterface $searchRepository,
        IriGeneratorInterface $variationIriGenerator
    );

    /**
     * @param DescriptionEdited $descriptionEdited
     */
    public function applyDescriptionEdited(DescriptionEdited $descriptionEdited);

    /**
     * @param EventProjectedToJSONLD $eventProjectedToJSONLD
     */
    public function applyEventProjectedToJSONLD(EventProjectedToJSONLD $eventProjectedToJSONLD);

    /**
     * @param EventVariationDeleted $eventVariationDeleted
     */
    public function applyEventVariationDeleted(EventVariationDeleted $eventVariationDeleted);
}
