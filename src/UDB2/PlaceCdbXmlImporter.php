<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Place\Place;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class PlaceCdbXmlImporter implements PlaceImporterInterface, LoggerAwareInterface
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
     * @param string $placeId
     * @return Place|null
     */
    public function updatePlaceFromUDB2($placeId)
    {

    }

    /**
     * @param string $placeId
     * @return Place|null
     */
    public function createPlaceFromUDB2($placeId)
    {
        try {
            $placeXml = $this->cdbXmlService->getCdbXmlOfActor($placeId);

            $place = Place::importFromUDB2(
                $placeId,
                $placeXml,
                $this->cdbXmlService->getCdbXmlNamespaceUri()
            );

            $this->repository->add($place);

            return $place;
        } catch (\Exception $e) {
            $this->logger->notice(
                "Place creation in UDB3 failed with an exception",
                [
                    'exception' => $e,
                    'placeId' => $placeId
                ]
            );
        }
    }
}
