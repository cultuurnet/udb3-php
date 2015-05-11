<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\OrganizerRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Organizer\Organizer;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
class OrganizerRepository extends ActorRepository
{
    /**
     * @var OrganizerImporterInterface
     */
    protected $organizerImporter;

    public function __construct(
        RepositoryInterface $decoratee,
        EntryAPIImprovedFactory $entryAPIImprovedFactory,
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
        $organizer = $this->tryMultipleTimes(
            2,
            function () use ($id) {
                try {
                    $organizer = $this->decoratee->load($id);
                    return $organizer;
                } catch (AggregateNotFoundException $e) {
                    $organizer = $this->organizerImporter->createOrganizerFromUDB2(
                        $id
                    );

                    if ($organizer) {
                        return $organizer;
                    } else {
                        throw $e;
                    }
                }
            }
        );

        return $organizer;
    }

    /**
     * @param int $times
     * @param callable $callable
     * @return mixed
     */
    private function tryMultipleTimes($times, callable $callable)
    {
        $result = null;

        while ($times > 0) {
            $times--;

            try {
                $result = $callable($times);

                if (null !== $result) {
                    break;
                }
            } catch (\Exception $e) {
                if ($times == 0) {
                    throw $e;
                }
            }

            sleep(1);
        }

        return $result;
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
