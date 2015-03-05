<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\PlaceRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Place\Place;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
class PlaceRepository extends ActorRepository
{
    /**
     * @var PlaceImporterInterface
     */
    protected $placeImporter;

    public function __construct(
        RepositoryInterface $decoratee,
        EntryAPIImprovedFactory $entryAPIImprovedFactory,
        PlaceImporterInterface $placeImporter,
        array $eventStreamDecorators = array()
    ) {
        parent::__construct(
            $decoratee,
            $entryAPIImprovedFactory,
            $eventStreamDecorators
        );
        $this->placeImporter = $placeImporter;
    }

    public function load($id)
    {
        try {
            $place = $this->decoratee->load($id);
        } catch (AggregateNotFoundException $e) {
            $place = $this->placeImporter->createPlaceFromUDB2($id);

            if (!$place) {
                throw $e;
            }
        }

        return $place;
    }

    /**
     * Returns the type.
     * @return string
     */
    protected function getType()
    {
        return Place::class;
    }
}
