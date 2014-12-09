<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\LocalEntityService.
 */

namespace CultuurNet\UDB3;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;

class LocalEntityService implements EntityServiceInterface
{
    /**
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;

    /**
     * @var RepositoryInterface
     */
    protected $entityRepository;

    /**
     * Constructs the local entity service.
     *
     * @param DocumentRepositoryInterface $documentRepository
     * @param RepositoryInterface $entityRepository
     */
    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        RepositoryInterface $entityRepository
    ) {
        $this->documentRepository = $documentRepository;
        $this->entityRepository = $entityRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity($id)
    {
        /** @var JsonDocument $document */
        $document = $this->documentRepository->get($id);

        if ($document) {
            return $document->getRawBody();
        }

        // @todo subsequent load and add are necessary for UDB2 repository
        // decorator, but this particular code should be moved over to an
        // EntityService decorator
        try {
            $entity = $this->entityRepository->load($id);
            $this->entityRepository->add($entity);
        } catch (AggregateNotFoundException $e) {
            throw new EntityNotFoundException(
                sprintf('Entity with id: %s not found.', $id)
            );
        }

        /** @var JsonDocument $document */
        $document = $this->documentRepository->get($id);

        return $document->getRawBody();
    }

}
