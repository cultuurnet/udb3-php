<?php

namespace CultuurNet\UDB3\Offer;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

/**
 * Trait for image update commands and events.
 */
trait ImageUpdateTrait
{
    /**
     * The id of the media object that the new information applies to.
     * @var UUID
     */
    protected $mediaObjectId;

    /**
     * @var \ValueObjects\String\String
     */
    protected $description;

    /**
     * @var \ValueObjects\String\String
     */
    protected $copyrightHolder;

    /**
     * @param $itemId
     * @param UUID $mediaObjectId
     * @param \ValueObjects\String\String $description
     * @param \ValueObjects\String\String $copyrightHolder
     */
    public function __construct(
        $itemId,
        UUID $mediaObjectId,
        String $description,
        String $copyrightHolder
    ) {
        $this->itemId = $itemId;
        $this->mediaObjectId = $mediaObjectId;
        $this->description = $description;
        $this->copyrightHolder = $copyrightHolder;
    }

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @return UUID
     */
    public function getMediaObjectId()
    {
        return $this->mediaObjectId;
    }

    /**
     * @return String
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return String
     */
    public function getCopyrightHolder()
    {
        return $this->copyrightHolder;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return array(
            'item_id' => $this->itemId,
            'media_object_id' => (string) $this->mediaObjectId,
            'description' => (string) $this->description,
            'copyright_holder' => (string) $this->copyrightHolder
        );
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            new UUID($data['media_object_id']),
            new String($data['description']),
            new String($data['copyright_holder'])
        );
    }
}
