<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Offer\ReadModel\JSONLD;

use CultureFeed_Cdb_Item_Base;
use CultuurNet\UDB3\Cdb\DateTimeFactory;
use stdClass;

class CdbXMLItemBaseImporter
{
    /**
     * @param CultureFeed_Cdb_Item_Base $item
     * @param stdClass $jsonLD
     */
    public function importPublicationInfo(
        CultureFeed_Cdb_Item_Base $item,
        stdClass $jsonLD
    ) {
        $jsonLD->creator = $item->getCreatedBy();

        $itemCreationDate = $item->getCreationDate();

        if (!empty($itemCreationDate)) {
            // format using ISO-8601 with time zone designator
            $creationDate = DateTimeFactory::dateTimeFromDateString(
                $itemCreationDate
            );

            $jsonLD->created = $creationDate->format('c');
        }

        $itemLastUpdatedDate = $item->getLastUpdated();

        if (!empty($itemLastUpdatedDate)) {
            $lastUpdatedDate = DateTimeFactory::dateTimeFromDateString(
                $itemLastUpdatedDate
            );

            $jsonLD->modified = $lastUpdatedDate->format('c');
        }

        $jsonLD->publisher = $item->getOwner();
    }

    /**
     * @param CultureFeed_Cdb_Item_Base $item
     * @param stdClass $jsonLD
     */
    public function importAvailable(
        \CultureFeed_Cdb_Item_Base $item,
        \stdClass $jsonLD
    ) {
        $availableString = $item->getAvailableFrom();
        if ($availableString) {
            $available = DateTimeFactory::dateTimeFromDateString(
                $availableString
            );

            $jsonLD->available = $available->format('c');
        }
    }

    /**
     * @param CultureFeed_Cdb_Item_Base $item
     * @param stdClass $jsonLD
     */
    public function importExternalId(
        \CultureFeed_Cdb_Item_Base $item,
        \stdClass $jsonLD
    ) {
        $externalId = $item->getExternalId();
        if (empty($externalId)) {
            return;
        }

        $externalIdIsCDB = (strpos($externalId, 'CDB:') === 0);

        if (!property_exists($jsonLD, 'sameAs')) {
            $jsonLD->sameAs = [];
        }

        if (!$externalIdIsCDB) {
            if (!in_array($externalId, $jsonLD->sameAs)) {
                array_push($jsonLD->sameAs, $externalId);
            }
        }
    }
}
