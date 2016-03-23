<?php

namespace CultuurNet\UDB3\Media;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

class Image implements SerializableInterface
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

    public function __construct(
        UUID $id,
        MIMEType $mimeType,
        String $description,
        String $copyrightHolder,
        Url $sourceLocation
    ) {
        $this->mediaObjectId = $id;
        $this->mimeType = $mimeType;
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
     * @return MIMEType
     */
    public function getMimeType()
    {
        return $this->mimeType;
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
     * @return Url
     */
    public function getSourceLocation()
    {
        return $this->sourceLocation;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            new UUID($data['media_object_id']),
            new MIMEType($data['mime_type']),
            new String($data['description']),
            new String($data['copyright_holder']),
            Url::fromNative($data['source_location'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
            'media_object_id' => (string) $this->getMediaObjectId(),
            'mime_type' => (string) $this->getMimeType(),
            'description' => (string) $this->getDescription(),
            'copyright_holder' => (string) $this->getCopyrightHolder(),
            'source_location' => (string) $this->getSourceLocation()
        ];
    }
}
