<?php

/**
 * @file
 * Contains CultuurNet\UDB3\MediaObject.
 */

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;

/**
 * MediaObjects for UDB3.
 */
class MediaObject implements SerializableInterface, JsonLdSerializableInterface
{

    /**
     * Internal file id.
     */
     protected $internalId;

    /**
     * Url to the media object.
     *
     * @var string
     */
    protected $url;

    /**
     * Url to the thumbnail for the media object.
     *
     * @var string
     */
    protected $thumbnailUrl;

    /**
     * Description of the mediaobject.
     *
     * @var string
     */
    protected $description;


    /**
     * Copyright info.
     *
     * @var string
     */
    protected $copyrightHolder;

    public function __construct($url, $thumbnailUrl, $description, $copyrightHolder, $internalId = '')
    {
        $this->url = $url;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->description = $description;
        $this->copyrightHolder = $copyrightHolder;
        $this->internalId = $internalId;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnailUrl;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getCopyrightHolder()
    {
        return $this->copyrightHolder;
    }

    /**
     * @return string
     */
    public function getInternalId()
    {
        return $this->internalId;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static($data['url'], $data['thumbnail_url'], $data['description'], $data['copyright_holder'], $data['internal_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnailUrl,
            'description' => $this->description,
            'copyright_holder' => $this->copyrightHolder,
            'internal_id' => $this->internalId
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toJsonLd()
    {
        // Matches the serialized array.
        return [
            'url' => $this->url,
            'thumbnailUrl' => $this->thumbnailUrl,
            'description' => $this->description,
            'copyrightHolder' => $this->copyrightHolder
        ];
    }
}
