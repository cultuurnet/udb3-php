<?php

namespace CultuurNet\UDB3\Media;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Media\Events\MediaObjectCreated;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

/**
 * MediaObjects for UDB3.
 */
class MediaObject extends EventSourcedAggregateRoot
{
    /**
     * Mime type of the media object.
     *
     * @var MIMEType
     */
    protected $mimeType;

    /**
     * The id of the media object.
     *
     * @var UUID
     */
    protected $mediaObjectId;

    /**
     * Description of the media object.
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

    /**
     * The URL where the source file can be found.
     * @var Url
     */
    protected $sourceLocation;

    /**
     * @param UUID $id
     * @param MIMEType $mimeType
     * @param \ValueObjects\String\String $description
     * @param \ValueObjects\String\String $copyrightHolder
     * @param Url $sourceLocation
     *
     * @return MediaObject
     */
    public static function create(
        UUID $id,
        MIMEType $mimeType,
        String $description,
        String $copyrightHolder,
        Url $sourceLocation
    ) {
        $mediaObject = new self();
        $mediaObject->apply(
            new MediaObjectCreated(
                $id,
                $mimeType,
                $description,
                $copyrightHolder,
                $sourceLocation
            )
        );

        return $mediaObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->mediaObjectId;
    }

    protected function applyMediaObjectCreated(MediaObjectCreated $mediaObjectCreated)
    {
        $this->mediaObjectId = $mediaObjectCreated->getMediaObjectId();
        $this->mimeType = $mediaObjectCreated->getMimeType();
        $this->description = $mediaObjectCreated->getDescription();
        $this->copyrightHolder = $mediaObjectCreated->getCopyrightHolder();
        $this->sourceLocation = $mediaObjectCreated->getSourceLocation();
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
     * @return Url
     */
    public function getSourceLocation()
    {
        return $this->sourceLocation;
    }
}
