<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\ValueObject\SapiVersion;
use ValueObjects\StringLiteral\StringLiteral;

class SearchQueryFactory implements SearchQueryFactoryInterface
{
    /**
     * @var SapiVersion
     */
    private $sapiVersion;

    /**
     * @param SapiVersion $sapiVersion
     */
    public function __construct(SapiVersion $sapiVersion)
    {
        $this->sapiVersion = $sapiVersion;
    }

    /**
     * @inheritdoc
     */
    public function createFromConstraint(
        StringLiteral $constraint,
        StringLiteral $offerId
    ) {
        return new Query($this->createQueryString($constraint, $offerId));
    }

    /**
     * @inheritdoc
     */
    public function createFromConstraints(
        array $constraints,
        StringLiteral $offerId
    ) {
        $queryString = '';

        foreach ($constraints as $constraint) {
            if (strlen($queryString)) {
                $queryString .= ' OR ';
            }

            $queryString .= $this->createQueryString($constraint, $offerId);
        }

        return new Query($queryString);
    }

    /**
     * @param StringLiteral $constraint
     * @param StringLiteral $offerId
     * @return string
     */
    private function createQueryString(
        StringLiteral $constraint,
        StringLiteral $offerId
    ) {
        $constraintStr = '(' . strtolower($constraint->toNative()) . ')';
        $offerIdStr = $offerId->toNative();

        if ($this->sapiVersion->sameValueAs(SapiVersion::V3())) {
            $id = 'id';
        } else {
            $id = 'cdbid';
        }

        return '(' . $constraintStr . ' AND ' . $id . ':' . $offerIdStr . ')';
    }
}
