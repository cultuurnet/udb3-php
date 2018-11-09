<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Role\ReadModel\Constraints\UserConstraintsReadRepositoryInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Search\Results;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use ValueObjects\Number\Integer;
use ValueObjects\StringLiteral\StringLiteral;

class Sapi3UserPermissionMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserConstraintsReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userConstraintsReadRepository;

    /**
     * @var SearchQueryFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchQueryFactory;

    /**
     * @var SearchServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchService;

    /**
     * @var Sapi3UserPermissionMatcher
     */
    private $sapi3UserPermissionMatcher;

    protected function setUp(): void
    {
        $this->userConstraintsReadRepository = $this->createMock(
            UserConstraintsReadRepositoryInterface::class
        );

        $this->searchQueryFactory = $this->createMock(
            SearchQueryFactoryInterface::class
        );

        $this->searchService = $this->createMock(
            SearchServiceInterface::class
        );

        $this->sapi3UserPermissionMatcher = new Sapi3UserPermissionMatcher(
            $this->userConstraintsReadRepository,
            $this->searchQueryFactory,
            $this->searchService
        );
    }

    /**
     * @test
     * @dataProvider totalItemsDataProvider()
     * @param Integer $totalItems
     * @param bool $expected
     */
    public function it_does_match_offer_based_on_total_items_count_of_one(
        Integer $totalItems,
        bool $expected
    ): void {
        $userId = new StringLiteral('ff085fed-8500-4dd9-8ac0-459233c642f4');
        $permission = Permission::AANBOD_BEWERKEN();
        $constraints = [
            new StringLiteral('address.\*.postalCode:3000'),
        ];
        $offerId = new StringLiteral('625a4e74-a1ca-4bee-9e85-39869457d531');
        $query = new Query('(address.\*.postalCode:3000 AND id:625a4e74-a1ca-4bee-9e85-39869457d531)');

        $this->userConstraintsReadRepository->expects($this->once())
            ->method('getByUserAndPermission')
            ->with(
                $userId,
                $permission
            )
            ->willReturn($constraints);

        $this->searchQueryFactory->expects($this->once())
            ->method('createFromConstraints')
            ->with(
                $constraints,
                $offerId
            )
            ->willReturn(
                $query
            );

        $this->searchService->expects($this->once())
            ->method('search')
            ->with($query)
            ->willReturn(
                new Results(
                    new OfferIdentifierCollection(),
                    $totalItems
                )
            );

        $this->assertEquals(
            $expected,
            $this->sapi3UserPermissionMatcher->itMatchesOffer(
                $userId,
                $permission,
                $offerId
            )
        );
    }

    /**
     * @return array
     */
    public function totalItemsDataProvider(): array
    {
        return [
            [
                new Integer(1),
                true,
            ],
            [
                new Integer(0),
                false,
            ],
            [
                new Integer(2),
                false,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_does_not_match_offer_when_user_has_no_matching_constraints(): void
    {
        $userId = new StringLiteral('ff085fed-8500-4dd9-8ac0-459233c642f4');
        $permission = Permission::AANBOD_BEWERKEN();
        $offerId = new StringLiteral('625a4e74-a1ca-4bee-9e85-39869457d531');

        $this->userConstraintsReadRepository->expects($this->once())
            ->method('getByUserAndPermission')
            ->with(
                $userId,
                $permission
            )
            ->willReturn([]);

        $this->searchQueryFactory->expects($this->never())
            ->method('createFromConstraints');

        $this->searchService->expects($this->never())
            ->method('search');

        $this->assertFalse(
            $this->sapi3UserPermissionMatcher->itMatchesOffer(
                $userId,
                $permission,
                $offerId
            )
        );
    }
}
