<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Role\ReadModel\Constraints\UserConstraintsReadRepositoryInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\SearchAPI2\ResultSetPullParser;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;
use Guzzle\Http\Message\Response;
use ValueObjects\StringLiteral\StringLiteral;

class UserPermissionMatcherTest extends \PHPUnit_Framework_TestCase
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
     * @var IriGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventIriGenerator;

    /**
     * @var IriGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeIriGenerator;

    /**
     * @var ResultSetPullParser
     */
    private $resultSetPullParser;

    /**
     * @var UserPermissionMatcher
     */
    private $userPermissionMatcher;

    protected function setUp()
    {
        $this->userConstraintsReadRepository = $this->createMock(
            UserConstraintsReadRepositoryInterface::class
        );

        $this->searchQueryFactory = $this->createMock(
            SearchQueryFactoryInterface::class
        );

        $this->searchService = $this->createMock(SearchServiceInterface::class);

        $this->eventIriGenerator = $this->createMock(IriGeneratorInterface::class);
        $this->placeIriGenerator = $this->createMock(IriGeneratorInterface::class);
        $this->mockIriGenerators();
        $this->resultSetPullParser = new ResultSetPullParser(
            new \XMLReader(),
            $this->eventIriGenerator,
            $this->placeIriGenerator
        );

        $this->userPermissionMatcher = new UserPermissionMatcher(
            $this->userConstraintsReadRepository,
            $this->searchQueryFactory,
            $this->searchService,
            $this->resultSetPullParser
        );
    }

    /**
     * @test
     */
    public function it_does_match_offer_when_total_found_items_equals_one()
    {
        $userId = new StringLiteral('userId');
        $permission = Permission::AANBOD_BEWERKEN();
        $offerId = new StringLiteral('offerId');

        $this->mockGetByUserAndPermission([new StringLiteral('zipCode:3000')]);

        $this->mockCreateFromConstraints(new StringLiteral(
            '(zipcode:3000 AND cdbid:' . $offerId->toNative() . ')'
        ));

        $cdbXml = file_get_contents(
            __DIR__ . '/samples/single_search_results.xml'
        );
        $this->mockSearch(new Response('200', null, $cdbXml));

        $matches = $this->userPermissionMatcher->itMatchesOffer(
            $userId,
            $permission,
            $offerId
        );

        $this->assertTrue($matches);
    }

    /**
     * @test
     */
    public function it_does_not_match_offer_when_user_has_no_constraints()
    {
        $userId = new StringLiteral('userId');
        $permission = Permission::AANBOD_BEWERKEN();
        $offerId = new StringLiteral('offerId');

        $this->mockGetByUserAndPermission([]);

        $this->userConstraintsReadRepository->expects($this->once())
            ->method('getByUserAndPermission')
            ->with($userId, $permission);

        $this->searchQueryFactory->expects($this->never())
            ->method('createFromConstraints');

        $this->searchService->expects($this->never())
            ->method('search');

        $matches = $this->userPermissionMatcher->itMatchesOffer(
            $userId,
            $permission,
            $offerId
        );

        $this->assertFalse($matches);
    }

    /**
     * @test
     */
    public function it_does_not_match_offer_when_search_response_is_not_200()
    {
        $userId = new StringLiteral('userId');
        $permission = Permission::AANBOD_BEWERKEN();
        $offerId = new StringLiteral('offerId');

        $this->mockGetByUserAndPermission([new StringLiteral('zipCode:3000')]);

        $this->mockCreateFromConstraints(new StringLiteral(
            '(zipcode:3000 AND cdbid:' . $offerId->toNative() . ')'
        ));

        $this->mockSearch(new Response('400'));

        $matches = $this->userPermissionMatcher->itMatchesOffer(
            $userId,
            $permission,
            $offerId
        );

        $this->assertFalse($matches);
    }

    /**
     * @test
     */
    public function it_does_not_match_offer_when_total_found_items_not_equal_to_one()
    {
        $userId = new StringLiteral('userId');
        $permission = Permission::AANBOD_BEWERKEN();
        $offerId = new StringLiteral('offerId');

        $this->mockGetByUserAndPermission([new StringLiteral('zipCode:3000')]);

        $this->mockCreateFromConstraints(new StringLiteral(
            '(zipcode:3000 AND cdbid:' . $offerId->toNative() . ')'
        ));

        $cdbXml = file_get_contents(
            __DIR__ . '/samples/multiple_search_results.xml'
        );
        $this->mockSearch(new Response('200', null, $cdbXml));

        $matches = $this->userPermissionMatcher->itMatchesOffer(
            $userId,
            $permission,
            $offerId
        );

        $this->assertFalse($matches);
    }

    /**
     * @param array $constraints
     */
    private function mockGetByUserAndPermission(array $constraints)
    {
        $this->userConstraintsReadRepository->method('getByUserAndPermission')
            ->willReturn($constraints);
    }

    /**
     * @param StringLiteral $query
     */
    private function mockCreateFromConstraints(StringLiteral $query)
    {
        $this->searchQueryFactory->method('createFromConstraints')
            ->willReturn($query);
    }

    /**
     * @param Response $response
     */
    private function mockSearch(Response $response)
    {
        $this->searchService->method('search')
            ->willReturn($response);
    }

    private function mockIriGenerators()
    {
        $this->eventIriGenerator->method('iri')
            ->willReturn('http://www.udb3.be/');

        $this->placeIriGenerator->method('iri')
            ->willReturn('http://www.udb3.be/');
    }
}
