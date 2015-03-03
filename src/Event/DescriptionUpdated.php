<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\DescriptionUpdated.
 */

namespace CultuurNet\UDB3\Event;

/**
 * Description of DescriptionUpdated
 */
class DescriptionUpdated extends EventEvent
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
