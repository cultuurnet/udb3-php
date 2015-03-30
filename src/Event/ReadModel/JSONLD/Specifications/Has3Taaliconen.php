<?php

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications;

use ValueObjects\String\String;

class Has3Taaliconen implements EventSpecificationInterface
{
    use Labelable;

    public function isSatisfiedBy($eventLd)
    {
        return $this->hasLabel($eventLd, new String('drie_taaliconen'));
    }
}
