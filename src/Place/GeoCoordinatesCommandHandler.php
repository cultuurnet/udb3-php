<?php

namespace CultuurNet\UDB3\Place;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\Geocoding\GeocodingServiceInterface;
use CultuurNet\UDB3\Address\AddressFormatterInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Place\Commands\UpdateGeoCoordinatesFromAddress;

class GeoCoordinatesCommandHandler extends Udb3CommandHandler
{
    /**
     * @var RepositoryInterface
     */
    private $placeRepository;

    /**
     * @var AddressFormatterInterface
     */
    private $addressFormatter;

    /**
     * @var GeocodingServiceInterface
     */
    private $geocodingService;

    /**
     * @param RepositoryInterface $placeRepository
     * @param AddressFormatterInterface $addressFormatter
     * @param GeocodingServiceInterface $geocodingService
     */
    public function __construct(
        RepositoryInterface $placeRepository,
        AddressFormatterInterface $addressFormatter,
        GeocodingServiceInterface $geocodingService
    ) {
        $this->placeRepository = $placeRepository;
        $this->addressFormatter = $addressFormatter;
        $this->geocodingService = $geocodingService;
    }

    /**
     * @param UpdateGeoCoordinatesFromAddress $updateGeoCoordinates
     */
    public function handleUpdateGeoCoordinatesFromAddress(UpdateGeoCoordinatesFromAddress $updateGeoCoordinates)
    {
        $coordinates = $this->geocodingService->getCoordinates(
            $this->addressFormatter->format(
                $updateGeoCoordinates->getAddress()
            )
        );

        /** @var Place $place */
        $place = $this->placeRepository->load($updateGeoCoordinates->getItemId());
        $place->updateGeoCoordinates($coordinates);
        $this->placeRepository->save($place);
    }
}
