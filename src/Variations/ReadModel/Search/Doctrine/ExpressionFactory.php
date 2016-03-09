<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\ReadModel\Search\Doctrine;

use CultuurNet\UDB3\Variations\ReadModel\Search\Criteria;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

class ExpressionFactory
{
    public function createExpressionFromCriteria(
        ExpressionBuilder $exp,
        Criteria $criteria
    ) {
        $compositeExpression = $exp->andX();

        $eventUrl = $criteria->getEventUrl();
        if ($eventUrl) {
            $compositeExpression->add(
                $exp->eq('offer', $exp->literal((string)$eventUrl))
            );
        }

        $purpose = $criteria->getPurpose();
        if ($purpose) {
            $compositeExpression->add(
                $exp->eq('purpose', $exp->literal((string)$purpose))
            );
        }

        $ownerId = $criteria->getOwnerId();
        if ($ownerId) {
            $compositeExpression->add(
                $exp->eq('owner', $exp->literal((string)$ownerId))
            );
        }

        if (count($compositeExpression) === 0) {
            return null;
        }

        return $compositeExpression;
    }
}
