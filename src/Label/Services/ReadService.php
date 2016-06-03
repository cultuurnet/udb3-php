<?php

namespace CultuurNet\UDB3\Label\Services;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use ValueObjects\Identity\UUID;

class ReadService implements ReadServiceInterface
{
    /**
     * @var ReadRepositoryInterface
     */
    private $readRepository;

    /**
     * ReadService constructor.
     * @param ReadRepositoryInterface $readRepository
     */
    public function __construct(ReadRepositoryInterface $readRepository)
    {
        $this->readRepository = $readRepository;
    }

    /**
     * @inheritdoc
     */
    public function getByUuid(UUID $uuid)
    {
        return $this->readRepository->getByUuid($uuid);
    }
}
