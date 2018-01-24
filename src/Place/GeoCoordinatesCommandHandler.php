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
    private $defaultAddressFormatter;

    /**
     * @var AddressFormatterInterface
     */
    private $fallbackAddressFormatter;

    /**
     * @var GeocodingServiceInterface
     */
    private $geocodingService;

    /**
     * @param RepositoryInterface $placeRepository
     * @param AddressFormatterInterface $defaultAddressFormatter
     * @param AddressFormatterInterface $fallbackAddressFormatter
     * @param GeocodingServiceInterface $geocodingService
     */
    public function __construct(
        RepositoryInterface $placeRepository,
        AddressFormatterInterface $defaultAddressFormatter,
        AddressFormatterInterface $fallbackAddressFormatter,
        GeocodingServiceInterface $geocodingService
    ) {
        $this->placeRepository = $placeRepository;
        $this->defaultAddressFormatter = $defaultAddressFormatter;
        $this->fallbackAddressFormatter = $fallbackAddressFormatter;
        $this->geocodingService = $geocodingService;
    }

    /**
     * @param UpdateGeoCoordinatesFromAddress $updateGeoCoordinates
     */
    public function handleUpdateGeoCoordinatesFromAddress(UpdateGeoCoordinatesFromAddress $updateGeoCoordinates)
    {
        $coordinates = $this->geocodingService->getCoordinates(
            $this->defaultAddressFormatter->format(
                $updateGeoCoordinates->getAddress()
            )
        );

        if ($coordinates === null) {
            $coordinates = $this->geocodingService->getCoordinates(
                $this->fallbackAddressFormatter->format(
                    $updateGeoCoordinates->getAddress()
                )
            );
        }

        /** @var Place $place */
        $place = $this->placeRepository->load($updateGeoCoordinates->getItemId());
        $place->updateGeoCoordinates($coordinates);
        $this->placeRepository->save($place);
    }
}
