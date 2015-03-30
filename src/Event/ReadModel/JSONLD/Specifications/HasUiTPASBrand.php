<?php

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications;

use ValueObjects\String\String;

class HasUiTPASBrand implements EventSpecificationInterface
{
    use Labelable;

    public function isSatisfiedBy($eventLd)
    {
        return $this->hasLabel($eventLd, new String('UiTPAS'));
    }
}
