<?php

namespace CultuurNet\UDB3\ImageAsset;

use Broadway\Serializer\SerializableInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class ImageUploaded implements SerializableInterface
{
    /**
     * @var UUID
     */
    protected $fileId;

    /**
     * @var String
     */
    protected $description;

    /**
     * @var String
     */
    protected $copyrightHolder;

    public function __construct(
        UUID $fileId,
        String $description,
        String $copyrightHolder
    ) {
        $this->$fileId = $fileId;
        $this->$description = $description;
        $this->$copyrightHolder = $copyrightHolder;
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
     * @return array
     */
    public function serialize()
    {
        return array(
            'file_id' => $this->getFileId()->toNative(),
            'description' => $this->getDescription()->toNative(),
            'copyright_holder' => $this->getCopyrightHolder()->toNative(),
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            new UUID($data['file_id']),
            new String($data['description']),
            new String($data['copyright_holder'])
        );
    }
}