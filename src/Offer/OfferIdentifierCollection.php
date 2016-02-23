<?php

namespace CultuurNet\UDB3\Offer;

use TwoDotsTwice\Collection\AbstractCollection;

/**
 * @method OfferIdentifierInterface[] toArray()
 */
class OfferIdentifierCollection extends AbstractCollection
{
    protected function getValidObjectType()
    {
        return OfferIdentifierInterface::class;
    }
}
