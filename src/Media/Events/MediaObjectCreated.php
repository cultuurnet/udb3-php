<?php

namespace CultuurNet\UDB3\Media\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class MediaObjectCreated implements SerializableInterface
{
    /**
     * @var UUID
     */
    protected $mediaObjectId;

    /**
     * @var MIMEType
     */
    protected $mimeType;

    /**
     * @var String
     */
    protected $description;

    /**
     * @var String
     */
    protected $copyrightHolder;

    /**
     * @var Url
     */
    protected $sourceLocation;

    /**
     * MediaObjectCreated constructor.
     * @param UUID $id
     * @param MIMEType $fileType
     * @param \ValueObjects\StringLiteral\StringLiteral $description
     * @param \ValueObjects\StringLiteral\StringLiteral $copyrightHolder
     * @param Url $sourceLocation
     */
    public function __construct(
        UUID $id,
        MIMEType $fileType,
        StringLiteral $description,
        StringLiteral $copyrightHolder,
        Url $sourceLocation
    ) {
        $this->mediaObjectId = $id;
        $this->mimeType = $fileType;
        $this->description = $description;
        $this->copyrightHolder = $copyrightHolder;
        $this->sourceLocation = $sourceLocation;
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
     * @return MIMEType
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return Url
     */
    public function getSourceLocation()
    {
        return $this->sourceLocation;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'media_object_id' => $this->getMediaObjectId()->toNative(),
            'mime_type' => $this->getMimeType()->toNative(),
            'description' => $this->getDescription()->toNative(),
            'copyright_holder' => $this->getCopyrightHolder()->toNative(),
            'source_location' => (string) $this->getSourceLocation()
        );
    }

    /**
     * @param array $data
     *
     * @return MediaObjectCreated The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            new UUID($data['media_object_id']),
            new MIMEType($data['mime_type']),
            new StringLiteral($data['description']),
            new StringLiteral($data['copyright_holder']),
            Url::fromNative($data['source_location'])
        );
    }
}
