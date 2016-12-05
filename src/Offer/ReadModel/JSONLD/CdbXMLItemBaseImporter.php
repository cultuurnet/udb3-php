<?php

namespace CultuurNet\UDB3\Offer\ReadModel\JSONLD;

use CultureFeed_Cdb_Data_Detail;
use CultureFeed_Cdb_Data_File;
use CultureFeed_Cdb_Item_Base;
use CultuurNet\UDB3\Cdb\DateTimeFactory;
use CultuurNet\UDB3\Offer\WorkflowStatus;
use stdClass;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;

class CdbXMLItemBaseImporter
{
    /**
     * @var IriGeneratorInterface
     */
    private $mediaIriGenerator;

    /**
     * CdbXMLItemBaseImporter constructor.
     * @param IriGeneratorInterface $mediaIriGenerator
     */
    public function __construct(IriGeneratorInterface $mediaIriGenerator)
    {
        $this->mediaIriGenerator = $mediaIriGenerator;
    }

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

            $jsonLD->availableFrom = $available->format('c');
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

    /**
     * @param CultureFeed_Cdb_Item_Base $item
     * @param stdClass $jsonLD
     */
    public function importWorkflowStatus(
        CultureFeed_Cdb_Item_Base $item,
        \stdClass $jsonLD
    ) {
        $wfStatus = $item->getWfStatus();

        $workflowStatus = $wfStatus ? WorkflowStatus::fromNative($wfStatus) : WorkflowStatus::READY_FOR_VALIDATION();

        $jsonLD->workflowStatus = $workflowStatus->getName();
    }

    /**
     * @param CultureFeed_Cdb_Data_Detail $detail
     * @param stdClass $jsonLD
     */
    public function importMedia($detail, $jsonLD)
    {
        /**
         * @var CultureFeed_Cdb_Data_File[] $udb2MediaFiles
         */
        $udb2MediaFiles = $detail->getMedia()->byMediaTypes(
            [
                CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO,
                CultureFeed_Cdb_Data_File::MEDIA_TYPE_IMAGEWEB
            ]
        );

        $jsonMediaObjects = [];

        foreach ($udb2MediaFiles as $mediaFile) {
            $description = $mediaFile->getDescription();

            $jsonMediaObject = [
                '@id' => $this->mediaIriGenerator->iri($mediaFile),
                '@type' => 'schema:ImageObject',
                'contentUrl' => $mediaFile->getHLink(),
                'thumbnailUrl' => $mediaFile->getHLink(),
                'copyrightHolder' => $mediaFile->getCopyright(),
            ];

            empty($description) ?: $jsonMediaObject['description'] = $description;

            $jsonMediaObjects[] = $jsonMediaObject;
        }

        empty($jsonMediaObjects) ?: $jsonLD->mediaObject = $jsonMediaObjects;
    }

    /**
     * @param CultureFeed_Cdb_Data_Detail $detail
     * @param stdClass $jsonLD
     *
     * This is based on code found in the culturefeed theme.
     * @see https://github.com/cultuurnet/culturefeed/blob/master/culturefeed_agenda/theme/theme.inc#L266-L284
     */
    public function importPicture($detail, $jsonLD)
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
