<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\PlaceRepository.
 */

namespace CultuurNet\UDB3\UDB2\Place;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Place\Place;
use CultuurNet\UDB3\UDB2\ActorRepository;
use CultuurNet\UDB3\UDB2\EntryAPIImprovedFactory;
use CultuurNet\UDB3\UDB2\EntryAPIImprovedFactoryInterface;

/**
 * Repository decorator that synchronizes with UDB2.
 */
class PlaceRepository extends ActorRepository
{
    /**
     * @var PlaceImporterInterface
     */
    protected $placeImporter;

    public function __construct(
        RepositoryInterface $decoratee,
        EntryAPIImprovedFactoryInterface $entryAPIImprovedFactory,
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
