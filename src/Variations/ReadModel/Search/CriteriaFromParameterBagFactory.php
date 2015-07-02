<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\ReadModel\Search;

use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use Symfony\Component\HttpFoundation\ParameterBag;

class CriteriaFromParameterBagFactory
{
    /**
     * @param ParameterBag $bag
     * @return Criteria
     */
    public function createCriteriaFromParameterBag(ParameterBag $bag)
    {
        $criteria = new Criteria();

        if ($bag->has('owner')) {
            $criteria = $criteria->withOwnerId(new OwnerId(
                $bag->get('owner')
            ));
        }

        if ($bag->has('purpose')) {
            $criteria = $criteria->withPurpose(new Purpose(
                $bag->get('purpose')
            ));
        }

        if ($bag->has('same_as')) {
            $criteria = $criteria->withEventUrl(new Url(
                $bag->get('same_as')
            ));
        }

        return $criteria;
    }
}
