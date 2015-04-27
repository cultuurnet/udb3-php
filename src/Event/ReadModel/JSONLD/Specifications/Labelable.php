<?php

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications;

use ValueObjects\String\String;

trait Labelable
{

    /**
     * @param $eventLd
     * @return bool
     */
    public function hasLabel($eventLd, String $label)
    {
        if ($label->isEmpty()) {
            throw new \InvalidArgumentException('Label can not be empty');
        }

        return property_exists($eventLd, 'labels') &&
                is_array($eventLd->labels) &&
                in_array((string)$label, $eventLd->labels);
    }
}
