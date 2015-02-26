<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\DescriptionUpdated.
 */

namespace CultuurNet\UDB3\Place;

/**
 * Description of DescriptionUpdated
 */
class DescriptionUpdated extends PlaceEvent
{
    use \CultuurNet\UDB3\DescriptionUpdatedTrait;

    /**
     * @param string $id
     * @param string $description
     */
    public function __construct($id, $description)
    {
        parent::__construct($id);
        $this->description = $description;
    }
}
