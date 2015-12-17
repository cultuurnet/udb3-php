<?php

namespace CultuurNet\UDB3\Media;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\JsonLdSerializableInterface;
use CultuurNet\UDB3\Media\Events\MediaObjectCreated;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

/**
 * MediaObjects for UDB3.
 */
class MediaObject extends EventSourcedAggregateRoot implements SerializableInterface, JsonLdSerializableInterface
{

    /**
     * Mime type of the media object.
     *
     * @var MIMEType
     */
    protected $mimeType;

    /**
     * File id.
     *
     * @var UUID
     */
    protected $fileId;

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

    public static function create(UUID $fileId, MIMEType $fileType, String $description, String $copyrightHolder)
    {
        $mediaObject = new self();
        $mediaObject->apply(new MediaObjectCreated($fileId, $fileType, $description, $copyrightHolder));

        return $mediaObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->fileId;
    }

    protected function applyMediaObjectCreated(MediaObjectCreated $mediaObjectCreated)
    {
        $this->fileId = $mediaObjectCreated->getFileId();
        $this->mimeType = $mediaObjectCreated->getMimeType();
        $this->description = $mediaObjectCreated->getDescription();
        $this->copyrightHolder = $mediaObjectCreated->getCopyrightHolder();
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
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @return MIMEType
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static($data['url'], $data['mime_type'], $data['thumbnail_url'], $data['description'], $data['copyright_holder'], $data['file_id'], $type);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
            'mime_type' => $this->mimeType,
            'url' => (string) $this->url,
            'thumbnail_url' => (string) $this->thumbnailUrl,
            'description' => (string) $this->description,
            'copyright_holder' => (string) $this->copyrightHolder,
            'file_id' => (string) $this->fileId,
        ];
    }

    /**
     * {@inheritdoc}
     * TODO: This should probably be moved to a projector
     */
    public function toJsonLd()
    {
        $jsonLd = [
            // TODO: use an iri generator to generate a proper id
            '@id' => (string) $this->getFileId(),
            // TODO: base type off of MIME
            '@type' => 'schema:MediaObject',
            'contentUrl' => (string) $this->url,
            'thumbnailUrl' => (string) $this->thumbnailUrl,
            'description' => (string) $this->description,
            'copyrightHolder' => (string) $this->copyrightHolder,
        ];

        return $jsonLd;
    }

    public function setUrl(Url $url)
    {
        $this->url = $url;
        $this->thumbnailUrl = $url;
    }
}
