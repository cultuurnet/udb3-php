<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\UDB3\Role\ReadModel\Constraints\UserConstraintsReadRepositoryInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use ValueObjects\StringLiteral\StringLiteral;

class Sapi3UserPermissionMatcher implements UserPermissionMatcherInterface
{
    /**
     * @var UserConstraintsReadRepositoryInterface
     */
    private $userConstraintsReadRepository;

    /**
     * @var SearchQueryFactoryInterface
     */
    private $searchQueryFactory;

    /**
     * @var SearchServiceInterface
     */
    private $searchService;

    /**
     * ConstraintsOfferFilter constructor.
     * @param UserConstraintsReadRepositoryInterface $userConstraintsReadRepository
     * @param SearchQueryFactoryInterface $searchQueryFactory
     * @param SearchServiceInterface $searchService
     */
    public function __construct(
        UserConstraintsReadRepositoryInterface $userConstraintsReadRepository,
        SearchQueryFactoryInterface $searchQueryFactory,
        SearchServiceInterface $searchService
    ) {
        $this->userConstraintsReadRepository = $userConstraintsReadRepository;
        $this->searchQueryFactory = $searchQueryFactory;
        $this->searchService = $searchService;
    }

    /**
     * @inheritdoc
     */
    public function itMatchesOffer(
        StringLiteral $userId,
        Permission $permission,
        StringLiteral $offerId
    ) {
        $constraints = $this->userConstraintsReadRepository->getByUserAndPermission(
            $userId,
            $permission
        );
        if (count($constraints) < 1) {
            return false;
        }

        $query = $this->searchQueryFactory->createFromConstraints(
            $constraints,
            $offerId
        );

        $results = $this->searchService->search($query);

        return ($results->getTotalItems()->toNative() === 1);
    }
}
