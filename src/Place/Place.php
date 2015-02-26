<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\Place.
 */

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Actor\Actor;

class Place extends Actor
{

    /**
     * @param string $description
     */
    public function updateDescription($description)
    {
        $this->apply(new DescriptionUpdated($this->actorId, $description));
    }

}
