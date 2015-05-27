<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedLabelsMemory;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Label;

class DefaultUsedLabelsMemoryService implements UsedLabelsMemoryServiceInterface
{
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function rememberLabelUsed($userId, Label $keyword)
    {
        $usedLabelsMemory = $this->ensureCreated($userId);

        $usedLabelsMemory->labelUsed($keyword);

        $this->repository->save($usedLabelsMemory);
    }

    /**
     * {@inheritdoc}
     */
    public function getMemory($userId)
    {
        $usedLabelsMemory = $this->ensureCreated($userId);

        return $usedLabelsMemory;
    }

    /**
     * @param string $userId
     * @return UsedLabelsMemory
     */
    protected function ensureCreated($userId)
    {
        try {
            $usedLabelsMemory = $this->repository->load($userId);
        } catch (AggregateNotFoundException $e) {
            $usedLabelsMemory = UsedLabelsMemory::create($userId);
        }
        return $usedLabelsMemory;
    }
}
