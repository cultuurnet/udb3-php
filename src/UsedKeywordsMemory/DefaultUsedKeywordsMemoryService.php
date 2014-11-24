<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Keyword;

class DefaultUsedKeywordsMemoryService implements UsedKeywordsMemoryServiceInterface
{
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function rememberKeywordUsed($userId, Keyword $keyword)
    {
        $usedKeywordsMemory = $this->ensureCreated($userId);

        $usedKeywordsMemory->keywordUsed($keyword);

        $this->repository->add($usedKeywordsMemory);
    }

    /**
     * {@inheritdoc}
     */
    public function getMemory($userId)
    {
        $usedKeywordsMemory = $this->ensureCreated($userId);

        return $usedKeywordsMemory;
    }

    /**
     * @param string $userId
     * @return UsedKeywordsMemory
     */
    protected function ensureCreated($userId)
    {
        try {
            $usedKeywordsMemory = $this->repository->load($userId);
        } catch (AggregateNotFoundException $e) {
            $usedKeywordsMemory = UsedKeywordsMemory::create($userId);
        }
        return $usedKeywordsMemory;
    }
}
