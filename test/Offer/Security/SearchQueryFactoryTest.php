<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\Search\Parameter\Query;
use ValueObjects\StringLiteral\StringLiteral;

class SearchQueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchQueryFactoryInterface
     */
    private $searchQueryFactory;

    protected function setUp()
    {
        $this->searchQueryFactory = new SearchQueryFactory();
    }

    /**
     * @test
     */
    public function it_creates_a_query_from_a_constraint()
    {
        $constraint = new StringLiteral('zipCode:3000 OR zipCode:3010');
        $offerId = new StringLiteral('offerId');

        $query = $this->searchQueryFactory->createFromConstraint(
            $constraint,
            $offerId
        );

        $expectedQuery = new Query($this->createQueryString($constraint, $offerId));

        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * @test
     */
    public function it_creates_a_query_from_constraints()
    {
        $constraint1 = new StringLiteral('zipCode:3000 OR zipCode:3010');
        $constraint2 = new StringLiteral('zipCode:3271 OR zipCode:3271');

        $offerId = new StringLiteral('offerId');

        $query = $this->searchQueryFactory->createFromConstraints(
            [$constraint1, $constraint2],
            $offerId
        );

        $queryStr1 = $this->createQueryString($constraint1, $offerId);
        $queryStr2 = $this->createQueryString($constraint2, $offerId);
        $expectedQuery = new Query($queryStr1 . ' OR ' . $queryStr2);

        $this->assertEquals($expectedQuery, $query);
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
        $constraintStr = strtolower($constraint->toNative());
        $offerIdStr = $offerId->toNative();

        return '((' . $constraintStr . ') AND cdbid:' . $offerIdStr . ')';
    }
}
