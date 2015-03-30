<?php

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications;

use ValueObjects\String\String;

class Has1Taalicoon implements EventSpecificationInterface
{
    use Labelable;

    public function isSatisfiedBy($eventLd)
    {
        return $this->hasLabel($eventLd, new String('een_taalicoon'));
    }
}
