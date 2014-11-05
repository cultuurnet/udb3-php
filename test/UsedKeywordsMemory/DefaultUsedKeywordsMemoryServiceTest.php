<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;


use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;

class DefaultUsedKeywordsMemoryServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultUsedKeywordsMemoryService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RepositoryInterface
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->repository = $this->getMock(
            'Broadway\\Repository\\RepositoryInterface'
        );
        $this->service = new DefaultUsedKeywordsMemoryService(
            $this->repository
        );
    }

    /**
     * @test
     */
    public function it_remembers_keywords_per_user_in_a_repository()
    {
        $userId = 1;
        $keyword = 'classical rock';

        $usedKeywordsMemory = $this->getMock(
            'CultuurNet\\UDB3\\UsedKeywordsMemory\\UsedKeywordsMemory'
        );

        $this->repository->expects($this->once())
            ->method('load')
            ->with($userId)
            ->will($this->returnValue($usedKeywordsMemory));

        $usedKeywordsMemory->expects($this->once())
            ->method('keywordUsed')
            ->with($keyword);

        $this->repository->expects(($this->once()))
            ->method('add')
            ->with($usedKeywordsMemory);

        $this->service->rememberKeywordUsed($userId, $keyword);
    }

    /**
     * @test
     */
    public function it_initiates_an_empty_memory_for_new_users()
    {
        $userId = 2;
        $keyword = 'jazz';

        $this->repository->expects($this->once())
            ->method('load')
            ->with($userId)
            ->will(
                $this->throwException(
                    new AggregateNotFoundException($userId)
                )
            );

        $expectedUsedKeywordsMemory = UsedKeywordsMemory::create($userId);
        $expectedUsedKeywordsMemory->keywordUsed($keyword);

        $this->repository->expects($this->once())
            ->method('add')
            ->with($expectedUsedKeywordsMemory);

        $this->service->rememberKeywordUsed($userId, $keyword);
    }
} 
