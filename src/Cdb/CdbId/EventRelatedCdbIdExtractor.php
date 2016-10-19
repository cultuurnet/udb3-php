<?php

namespace CultuurNet\UDB3\Cdb\CdbId;

use CultuurNet\UDB3\Cdb\ExternalId\MappingServiceInterface;

class EventRelatedCdbIdExtractor implements EventRelatedCdbIdExtractorInterface
{
    /**
     * @var MappingServiceInterface
     */
    private $externalIdMappingService;

    /**
     * @param MappingServiceInterface $externalIdMappingService
     */
    public function __construct(
        MappingServiceInterface $externalIdMappingService
    ) {
        $this->externalIdMappingService = $externalIdMappingService;
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $cdbEvent
     * @return string|null
     */
    public function getRelatedPlaceCdbId(\CultureFeed_Cdb_Item_Event $cdbEvent)
    {
        $cdbPlace = $cdbEvent->getLocation();

        if (!is_null($cdbPlace)) {
            return $this->getCdbIdFromEmbeddedLocationOrOrganizer($cdbPlace);
        } else {
            return null;
        }
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $cdbEvent
     * @return string|null
     */
    public function getRelatedOrganizerCdbId(\CultureFeed_Cdb_Item_Event $cdbEvent)
    {
        $cdbOrganizer = $cdbEvent->getOrganiser();

        if (!is_null($cdbOrganizer)) {
            return $this->getCdbIdFromEmbeddedLocationOrOrganizer($cdbOrganizer);
        } else {
            return null;
        }
    }

    /**
     * @param \CultureFeed_Cdb_Data_Location|\CultureFeed_Cdb_Data_Organiser $embeddedCdb
     * @return string|null
     */
    private function getCdbIdFromEmbeddedLocationOrOrganizer($embeddedCdb)
    {
        if (!is_null($embeddedCdb->getCdbid())) {
            return $embeddedCdb->getCdbid();
        }

        if (!is_null($embeddedCdb->getExternalId())) {
            return $this->externalIdMappingService->getCdbId(
                $embeddedCdb->getExternalId()
            );
        }

        if (!is_null($embeddedCdb->getActor()) && !is_null($embeddedCdb->getActor()->getCdbId())) {
            return $embeddedCdb->getActor()->getCdbId();
        }

        if (!is_null($embeddedCdb->getActor()) && !is_null($embeddedCdb->getActor()->getExternalId())) {
            return $this->externalIdMappingService->getCdbId(
                $embeddedCdb->getActor()->getExternalId()
            );
        }

        return null;
    }
}
