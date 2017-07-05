<?php

namespace CultuurNet\UDB3\Media;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\Media\Properties\CopyrightHolder;
use CultuurNet\UDB3\Media\Properties\Description;
use ValueObjects\Identity\UUID;
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
     * @var Description
     */
    protected $description;

    /**
     * @var CopyrightHolder
     */
    protected $copyrightHolder;

    /**
     * @var Url
     */
    protected $sourceLocation;

    /**
     * @var Language
     */
    protected $language;

    public function __construct(
        UUID $id,
        MIMEType $mimeType,
        Description $description,
        CopyrightHolder $copyrightHolder,
        Url $sourceLocation,
        Language $language
    ) {
        $this->mediaObjectId = $id;
        $this->mimeType = $mimeType;
        $this->description = $description;
        $this->copyrightHolder = $copyrightHolder;
        $this->sourceLocation = $sourceLocation;
        $this->language = $language;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
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
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return CopyrightHolder
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
            new Description($data['description']),
            new CopyrightHolder($data['copyright_holder']),
            Url::fromNative($data['source_location']),
            array_key_exists('language', $data) ? new Language($data['language']) : new Language('nl')
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
            'source_location' => (string) $this->getSourceLocation(),
            'language' => (string) $this->getLanguage(),
        ];
    }
}
