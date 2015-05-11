<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\OrganizerRepository.
 */

namespace CultuurNet\UDB3\UDB2\Organizer;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Organizer\Organizer;
use CultuurNet\UDB3\UDB2\ActorRepository;
use CultuurNet\UDB3\UDB2\EntryAPIImprovedFactoryInterface;

/**
 * Repository decorator that synchronizes with UDB2.
 */
class OrganizerRepository extends ActorRepository
{
    /**
     * @var OrganizerImporterInterface
     */
    protected $organizerImporter;

    public function __construct(
        RepositoryInterface $decoratee,
        EntryAPIImprovedFactoryInterface $entryAPIImprovedFactory,
        OrganizerImporterInterface $organizerImporter,
        array $eventStreamDecorators = array()
    ) {
        parent::__construct(
            $decoratee,
            $entryAPIImprovedFactory,
            $eventStreamDecorators
        );
        $this->organizerImporter = $organizerImporter;
    }

    public function load($id)
    {
        try {
            $organizer = $this->decoratee->load($id);
        } catch (AggregateNotFoundException $e) {
            $organizer = $this->organizerImporter->createOrganizerFromUDB2($id);

            if (!$organizer) {
                throw $e;
            }
        }

        return $organizer;
    }

    /**
     * Returns the type.
     * @return string
     */
    protected function getType()
    {
        return Organizer::class;
    }
}
