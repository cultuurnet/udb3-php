<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;


use Broadway\Repository\RepositoryInterface;

class DefaultUsedKeywordsMemoryService implements UsedKeywordsMemoryServiceInterface
{

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function rememberKeywordUsed($userId, $keyword)
    {
        /** @var UsedKeywordsMemory $usedKeywordsMemory */
        try {
            $usedKeywordsMemory = $this->repository->load(
                $userId
            );
        } catch (\Broadway\Repository\AggregateNotFoundException $e) {
            $usedKeywordsMemory = UsedKeywordsMemory::create($userId);

        }

        $usedKeywordsMemory->keywordUsed($keyword);

        $this->repository->add($usedKeywordsMemory);
    }
} 
