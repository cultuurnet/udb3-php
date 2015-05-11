<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Organizer;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Organizer\Organizer;
use CultuurNet\UDB3\UDB2\ActorCdbXmlServiceInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Imports organizers from UDB2 into UDB3 based on cdbxml.
 */
class OrganizerCdbXmlImporter implements OrganizerImporterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ActorCdbXmlServiceInterface
     */
    protected $cdbXmlService;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @param ActorCdbXmlServiceInterface $cdbXmlService
     * @param RepositoryInterface $repository
     */
    public function __construct(
        ActorCdbXmlServiceInterface $cdbXmlService,
        RepositoryInterface $repository
    ) {
        $this->cdbXmlService = $cdbXmlService;
        $this->repository = $repository;
    }

    /**
     * @param string $organizerId
     * @return Organizer|null
     */
    public function updateOrganizerFromUDB2($organizerId)
    {

    }

    /**
     * @param string $organizerId
     * @return Organizer|null
     */
    public function createOrganizerFromUDB2($organizerId)
    {
        try {
            $organizerXml = $this->cdbXmlService->getCdbXmlOfActor($organizerId);

            $organizer = Organizer::importFromUDB2(
                $organizerId,
                $organizerXml,
                $this->cdbXmlService->getCdbXmlNamespaceUri()
            );

            $this->repository->add($organizer);

            return $organizer;
        } catch (\Exception $e) {
            $this->logger->notice(
                "Organizer creation in UDB3 failed with an exception",
                [
                    'exception' => $e,
                    'organizerId' => $organizerId
                ]
            );
        }
    }
}
