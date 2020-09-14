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
final class MediaObject implements SerializableInterface, JsonLdSerializableInterface
{

    /**
     * Type of media object.
     * @var string|null
     */
    protected $type;

    /**
     * @var string
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

    public function __construct(string $url, string $thumbnailUrl, string $description, string $copyrightHolder, string $internalId = '', ?string $type = null)
    {
        $this->type = $type;
        $this->url = $url;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->description = $description;
        $this->copyrightHolder = $copyrightHolder;
        $this->internalId = $internalId;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getThumbnailUrl(): string
    {
        return $this->thumbnailUrl;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getCopyrightHolder(): string
    {
        return $this->copyrightHolder;
    }

    /**
     * @return string
     */
    public function getInternalId(): string
    {
        return $this->internalId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data): MediaObject
    {
        $type = !empty($data['type']) ? $data['type'] : null;
        return new self($data['url'], $data['thumbnail_url'], $data['description'], $data['copyright_holder'], $data['internal_id'], $type);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'type' => $this->type,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnailUrl,
            'description' => $this->description,
            'copyright_holder' => $this->copyrightHolder,
            'internal_id' => $this->internalId,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toJsonLd(): array
    {
        $jsonLd = [];
        if (!empty($this->type)) {
            $jsonLd['@type'] = $this->type;
        }

        $jsonLd['url'] = $this->url;
        $jsonLd['thumbnailUrl'] = $this->thumbnailUrl;
        $jsonLd['description'] = $this->description;
        $jsonLd['copyrightHolder'] = $this->copyrightHolder;

        return $jsonLd;

    }
}
