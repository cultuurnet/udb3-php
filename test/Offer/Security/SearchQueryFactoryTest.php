<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\ValueObject\SapiVersion;
use ValueObjects\StringLiteral\StringLiteral;

class SearchQueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider dataProvider()
     * @param SapiVersion $sapiVersion
     */
    public function it_creates_a_query_from_a_constraint(SapiVersion $sapiVersion)
    {
        $searchQueryFactory = new SearchQueryFactory($sapiVersion);

        $constraint = new StringLiteral('zipCode:3000 OR zipCode:3010');
        $offerId = new StringLiteral('offerId');

        $query = $searchQueryFactory->createFromConstraint(
            $constraint,
            $offerId
        );

        $expectedQuery = new Query($this->createQueryString($sapiVersion, $constraint, $offerId));

        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * @test
     * @dataProvider dataProvider()
     * @param SapiVersion $sapiVersion
     */
    public function it_creates_a_query_from_constraints(SapiVersion $sapiVersion)
    {
        $searchQueryFactory = new SearchQueryFactory($sapiVersion);

        $constraint1 = new StringLiteral('zipCode:3000 OR zipCode:3010');
        $constraint2 = new StringLiteral('zipCode:3271 OR zipCode:3271');

        $offerId = new StringLiteral('offerId');

        $query = $searchQueryFactory->createFromConstraints(
            [$constraint1, $constraint2],
            $offerId
        );

        $queryStr1 = $this->createQueryString($sapiVersion, $constraint1, $offerId);
        $queryStr2 = $this->createQueryString($sapiVersion, $constraint2, $offerId);
        $expectedQuery = new Query($queryStr1 . ' OR ' . $queryStr2);

        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            [
                SapiVersion::V2(),
            ],
            [
                SapiVersion::V3(),
            ],
        ];
    }

    /**
     * @param SapiVersion $sapiVersion
     * @param StringLiteral $constraint
     * @param StringLiteral $offerId
     * @return string
     */
    private function createQueryString(
        SapiVersion $sapiVersion,
        StringLiteral $constraint,
        StringLiteral $offerId
    ) {
        $constraintStr = strtolower($constraint->toNative());
        $offerIdStr = $offerId->toNative();

        if ($sapiVersion->sameValueAs(SapiVersion::V3())) {
            $id = 'id';
        } else {
            $id = 'cdbid';
        }

        return '((' . $constraintStr . ') AND ' . $id . ':' . $offerIdStr . ')';
    }
}
