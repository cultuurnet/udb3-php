<?php

namespace CultuurNet\UDB3\Media;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;
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
     * @var StringLiteral
     */
    protected $description;

    /**
     * @var StringLiteral
     */
    protected $copyrightHolder;

    /**
     * @var Url
     */
    protected $sourceLocation;

    public function __construct(
        UUID $id,
        MIMEType $mimeType,
        StringLiteral $description,
        StringLiteral $copyrightHolder,
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
     * @return StringLiteral
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return StringLiteral
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
            new StringLiteral($data['description']),
            new StringLiteral($data['copyright_holder']),
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
