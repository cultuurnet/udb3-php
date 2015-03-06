<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\ReadModel\Relations;

use Broadway\ReadModel\RepositoryInterface;
use CultuurNet\UDB3\Place\Events\OrganizerDeleted;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\ReadModel\Udb3Projector;

class Projector extends Udb3Projector
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store the relation for places imported from UDB2.
     */
    protected function applyPlaceImportedFromUDB2(PlaceImportedFromUDB2 $place)
    {

        // No relation exists in UDB2.
        $placeId = $place->getPlaceId();
        $this->storeRelations($placeId, null);
    }

    /**
     * Store the relation when the organizer was changed
     */
    protected function applyOrganizerUpdated(OrganizerUpdated $organizerUpdated)
    {
        $this->storeRelations($organizerUpdated->getPlaceId(), $organizerUpdated->getOrganizerId());
    }

    /**
     * Remove the relation.
     */
    protected function applyOrganizerDeleted(OrganizerDeleted $organizerDeleted)
    {
        $this->storeRelations($organizerDeleted->getPlaceId(), null);
    }

    /**
     * Store the relation.
     */
    protected function storeRelations($placeId, $organizerId)
    {
        $this->repository->storeRelations($placeId, $organizerId);
    }
}
