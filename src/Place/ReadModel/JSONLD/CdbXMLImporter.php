<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

/**
 * Takes care of importing actors in the CdbXML format (UDB2) that represent
 * a place, into a UDB3 JSON-LD document.
 */
class CdbXMLImporter
{
    /**
     * Imports a UDB2 organizer actor into a UDB3 JSON-LD document.
     *
     * @param \stdClass $base
     *   The JSON-LD document object to start from.
     * @param \CultureFeed_Cdb_Item_Actor $actor
     *   The actor data from UDB2 to import.
     *
     * @return \stdClass
     *   A new JSON-LD document object with the UDB2 actor data merged in.
     */
    public function documentWithCdbXML(
        $base,
        \CultureFeed_Cdb_Item_Actor $actor
    ) {
        $jsonLD = clone $base;
        
        $detail = null;

        /** @var \CultureFeed_Cdb_Data_ActorDetail[] $details */
        $details = $actor->getDetails();

        foreach ($details as $languageDetail) {
            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!$detail) {
                $detail = $languageDetail;
            }
        }

        $descriptions = [
            trim($detail->getShortDescription()),
            trim($detail->getLongDescription())
        ];
        $descriptions = array_filter($descriptions);
        if (count($descriptions) > 0) {
            $jsonLD->description = implode('<br/>', $descriptions);
        }

        $jsonLD->name = $detail->getTitle();

        // Address
        $contact_cdb = $actor->getContactInfo();
        if ($contact_cdb) {
            $addresses = $contact_cdb->getAddresses();

            foreach ($addresses as $address) {
                $address = $address->getPhysicalAddress();

                if ($address) {
                    $jsonLD->address = array(
                        'addressCountry' => $address->getCountry(),
                        'addressLocality' => $address->getCity(),
                        'postalCode' => $address->getZip(),
                        'streetAddress' =>
                            $address->getStreet() . ' ' .
                            $address->getHouseNumber(),
                    );

                    break;
                }
            }
        }

        // Booking info.
        $bookingInfo = array(
            'description' => '',
            'name' => 'standard price',
            'price' => 0.0,
            'priceCurrency' => 'EUR',
        );
        $price = $detail->getPrice();

        if ($price) {
            $bookingInfo['description'] = floatval($price->getDescription());
            $bookingInfo['name'] = floatval($price->getTitle());
            $bookingInfo['price'] = floatval($price->getValue());
        }
        $jsonLD->bookingInfo = $bookingInfo;

        // Image.
        $images = $detail->getMedia()->byMediaType(
            \CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO
        );
        $images->rewind();
        $image = count($images) > 0 ? $images->current() : null;
        if ($image) {
            $jsonLD->image = $image->getHLink();
        }

        return $jsonLD;
    }
}
