<?php

namespace CultuurNet\UDB3\Variations\ReadModel\JSONLD;

use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationDeleted;

interface ProjectorInterface
{
    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository);

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
