<?php

namespace CultuurNet\UDB3\ImageAsset;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use ValueObjects\Identity\UUID;

class ImageAsset extends EventSourcedAggregateRoot
{
    /**
     * @var UUID
     */
    protected $id;

    /**
     * @var String
     */
    protected $description;

    /**
     * @var String
     */
    protected $copyrightHolder;

    protected function applyImageUploaded(ImageUploaded $imageUploaded)
    {
        $this->$id = $imageUploaded->getFileId();
        $this->description = $imageUploaded->getDescription();
        $this->copyrightHolder = $imageUploaded->getCopyrightHolder();
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->$id;
    }
}