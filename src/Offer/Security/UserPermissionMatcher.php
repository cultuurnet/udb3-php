<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\Search\Parameter\FilterQuery;
use CultuurNet\Search\Parameter\Group;
use CultuurNet\UDB3\Role\ReadModel\Constraints\UserConstraintsReadRepositoryInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\SearchAPI2\ResultSetPullParser;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;
use ValueObjects\StringLiteral\StringLiteral;

class UserPermissionMatcher implements UserPermissionMatcherInterface
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
     * @var ResultSetPullParser
     */
    private $resultSetPullParser;

    /**
     * ConstraintsOfferFilter constructor.
     * @param UserConstraintsReadRepositoryInterface $userConstraintsReadRepository
     * @param SearchQueryFactoryInterface $searchQueryFactory
     * @param SearchServiceInterface $searchService
     * @param ResultSetPullParser $resultSetPullParser
     */
    public function __construct(
        UserConstraintsReadRepositoryInterface $userConstraintsReadRepository,
        SearchQueryFactoryInterface $searchQueryFactory,
        SearchServiceInterface $searchService,
        ResultSetPullParser $resultSetPullParser
    ) {
        $this->userConstraintsReadRepository = $userConstraintsReadRepository;
        $this->searchQueryFactory = $searchQueryFactory;
        $this->searchService = $searchService;
        $this->resultSetPullParser = $resultSetPullParser;
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
        $response = $this->searchService->search(
            [
                $query,
                new FilterQuery('private:*'),
                new Group(true),
            ]
        );
        if ($response->getStatusCode() != 200) {
            return false;
        }

        $result = $this->resultSetPullParser->getResultSet($response->getBody());
        return ($result->getTotalItems()->toNative() === 1);
    }
}
