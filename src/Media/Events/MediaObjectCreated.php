<?php

namespace CultuurNet\UDB3\Media\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class MediaObjectCreated implements SerializableInterface
{
    /**
     * @var UUID
     */
    protected $fileId;

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
     * @param UUID $fileId
     * @param MIMEType $fileType
     * @param String $description
     * @param String $copyrightHolder
     */
    public function __construct(
        UUID $fileId,
        MIMEType $fileType,
        String $description,
        String $copyrightHolder
    ) {
        $this->fileId = $fileId;
        $this->mimeType = $fileType;
        $this->description = $description;
        $this->copyrightHolder = $copyrightHolder;
    }

    /**
     * @return UUID
     */
    public function getFileId()
    {
        return $this->fileId;
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
     * @return array
     */
    public function serialize()
    {
        return array(
            'file_id' => $this->getFileId()->toNative(),
            'mime_type' => $this->getMimeType()->toNative(),
            'description' => $this->getDescription()->toNative(),
            'copyright_holder' => $this->getCopyrightHolder()->toNative(),
        );
    }

    /**
     * @return MediaObjectCreated The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            new UUID($data['file_id']),
            new MIMEType($data['mime_type']),
            new String($data['description']),
            new String($data['copyright_holder'])
        );
    }
}
