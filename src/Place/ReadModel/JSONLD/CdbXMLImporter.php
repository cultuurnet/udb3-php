<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use CultureFeed_Cdb_Data_File;
use CultuurNet\UDB3\LabelImporter;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;

/**
 * Takes care of importing actors in the CdbXML format (UDB2) that represent
 * a place, into a UDB3 JSON-LD document.
 */
class CdbXMLImporter
{
    /**
     * @var CdbXMLItemBaseImporter
     */
    private $cdbXMLItemBaseImporter;

    /**
     * @param CdbXMLItemBaseImporter $dbXMLItemBaseImporter
     */
    public function __construct(CdbXMLItemBaseImporter $dbXMLItemBaseImporter)
    {
        $this->cdbXMLItemBaseImporter = $dbXMLItemBaseImporter;
    }

    /**
     * Imports a UDB2 organizer actor into a UDB3 JSON-LD document.
     *
     * @param \stdClass                   $base
     *   The JSON-LD document object to start from.
     * @param \CultureFeed_Cdb_Item_Base $item
     *   The event/actor data from UDB2 to import.
     *
     * @return \stdClass
     *   A new JSON-LD document object with the UDB2 actor data merged in.
     */
    public function documentWithCdbXML(
        $base,
        \CultureFeed_Cdb_Item_Base $item
    ) {
        $jsonLD = clone $base;

        $detail = null;

        /** @var \CultureFeed_Cdb_Data_ActorDetail[] $details */
        $details = $item->getDetails();

        foreach ($details as $languageDetail) {
            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!$detail) {
                $detail = $languageDetail;
            }
        }

        // make sure the description is an object as well before trying to add
        // translations
        if (empty($jsonLD->description)) {
            $jsonLD->description = new \stdClass();
        }

        $descriptions = [
            trim($detail->getShortDescription()),
            trim($detail->getLongDescription())
        ];
        $descriptions = array_filter($descriptions);
        if (count($descriptions) > 0) {
            $jsonLD->description->nl = implode('<br/>', $descriptions);
        }

        // make sure the name is an object as well before trying to add
        // translations
        if (empty($jsonLD->name)) {
            $jsonLD->name = new \stdClass();
        }
        $jsonLD->name->nl = $detail->getTitle();

        $this->cdbXMLItemBaseImporter->importPublicationInfo($item, $jsonLD);
        $this->cdbXMLItemBaseImporter->importAvailable($item, $jsonLD);
        $this->cdbXMLItemBaseImporter->importExternalId($item, $jsonLD);
        $this->cdbXMLItemBaseImporter->importWorkflowStatus($item, $jsonLD);

        // Address
        $contact_cdb = $item->getContactInfo();
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

        $this->importPicture($detail, $jsonLD);

        $labelImporter = new LabelImporter();
        $labelImporter->importLabels($item, $jsonLD);

        $this->importTerms($item, $jsonLD);

        return $jsonLD;
    }

    public function eventDocumentWithCdbXML(
        $base,
        \CultureFeed_Cdb_Item_Base $item
    ) {
        $jsonLD = $this->documentWithCdbXML($base, $item);

        return $jsonLD;
    }

    /**
     * @param \CultureFeed_Cdb_Item_Actor $actor
     * @param \stdClass $jsonLD
     */
    private function importTerms(\CultureFeed_Cdb_Item_Base $actor, $jsonLD)
    {
        $themeBlacklist = [];
        $categories = array();
        foreach ($actor->getCategories() as $category) {
            /* @var \Culturefeed_Cdb_Data_Category $category */
            if ($category && !in_array($category->getName(), $themeBlacklist)) {
                $categories[] = array(
                    'label' => $category->getName(),
                    'domain' => $category->getType(),
                    'id' => $category->getId(),
                );
            }
        }
        $jsonLD->terms = $categories;
    }

    /**
     * @param \CultureFeed_Cdb_Data_ActorDetail $detail
     * @param \stdClass $jsonLD
     *
     * This is based on code found in the culturefeed theme.
     * @see https://github.com/cultuurnet/culturefeed/blob/master/culturefeed_agenda/theme/theme.inc#L266-L284
     */
    private function importPicture($detail, $jsonLD)
    {
        $mainPicture = null;

        // first check if there is a media file that is main and has the PHOTO media type
        $photos = $detail->getMedia()->byMediaType(CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO);
        foreach ($photos as $photo) {
            if ($photo->isMain()) {
                $mainPicture = $photo;
            }
        }

        // the IMAGEWEB media type is deprecated but can still be used as a main image if there is no PHOTO
        if (empty($mainPicture)) {
            $images = $detail->getMedia()->byMediaType(CultureFeed_Cdb_Data_File::MEDIA_TYPE_IMAGEWEB);
            foreach ($images as $image) {
                if ($image->isMain()) {
                    $mainPicture = $image;
                }
            }
        }

        // if there is no explicit main image we just use the oldest picture of any type
        if (empty($mainPicture)) {
            $pictures = $detail->getMedia()->byMediaTypes(
                [
                    CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO,
                    CultureFeed_Cdb_Data_File::MEDIA_TYPE_IMAGEWEB
                ]
            );

            $pictures->rewind();
            $mainPicture = count($pictures) > 0 ? $pictures->current() : null;
        }

        if ($mainPicture) {
            $jsonLD->image = $mainPicture->getHLink();
        }
    }
}
