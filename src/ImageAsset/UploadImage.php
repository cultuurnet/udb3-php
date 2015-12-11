<?php

namespace CultuurNet\UDB3\ImageAsset;

use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class UploadImage
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

    /**
     * @var string
     */
    protected $fileType;

    public function __construct(
        UUID $fileId,
        $fileType,
        String $description,
        String $copyrightHolder
    ) {
        $this->fileId = $fileId;
        $this->description = $description;
        $this->copyrightHolder = $copyrightHolder;
        $this->fileType = $fileType;
    }

    /**
     * @return Uuid
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
     * @return string
     */
    public function getFileType()
    {
        return $this->fileType;
    }
}
