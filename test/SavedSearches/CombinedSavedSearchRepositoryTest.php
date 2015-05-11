<?php

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\Properties\CreatedByQueryString;
use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearch;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearchRepositoryInterface;
use ValueObjects\String\String;
use ValueObjects\Web\EmailAddress;

class CombinedSavedSearchRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_combine_the_results_of_multiple_repositories()
    {
        $savedSearches = [
            new SavedSearch(
                new String('Saved search 0'),
                new QueryString('city:leuven')
            ),
            new SavedSearch(
                new String('Saved search 1'),
                new QueryString('city:herent')
            ),
            new SavedSearch(
                new String('Saved search 2'),
                new CreatedByQueryString(new EmailAddress('foo@bar.com'))
            ),
            new SavedSearch(
                new String('Saved search 3'),
                new QueryString('keyword:paspartoe')
            ),
        ];

        $firstRepository = $this->getMock(SavedSearchRepositoryInterface::class);
        $firstRepository->expects($this->once())
            ->method('ownedByCurrentUser')
            ->willReturn([
                $savedSearches[0],
            ]);

        $secondRepository = $this->getMock(SavedSearchRepositoryInterface::class);
        $secondRepository->expects($this->once())
            ->method('ownedByCurrentUser')
            ->willReturn([
                $savedSearches[1],
                $savedSearches[2],
            ]);

        $thirdRepository = $this->getMock(SavedSearchRepositoryInterface::class);
        $thirdRepository->expects($this->once())
            ->method('ownedByCurrentUser')
            ->willReturn([
                $savedSearches[3],
            ]);

        $combinedRepository = new CombinedSavedSearchRepository(
            $firstRepository,
            $secondRepository,
            $thirdRepository
        );

        $this->assertEquals($savedSearches, $combinedRepository->ownedByCurrentUser());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_a_provided_argument_is_not_a_repository()
    {
        $invalidRepository = new \stdClass();
        $this->setExpectedException(\InvalidArgumentException::class);
        new CombinedSavedSearchRepository($invalidRepository);
    }
}
