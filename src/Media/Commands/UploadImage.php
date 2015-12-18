<?php

namespace CultuurNet\UDB3\Media\Commands;

use CultuurNet\UDB3\Media\Properties\MIMEType;
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
     * @var MIMEType
     */
    protected $mimeType;

    /**
     * @param UUID $fileId
     * @param MIMEType $mimeType
     * @param String $description
     * @param String $copyrightHolder
     */
    public function __construct(
        UUID $fileId,
        MIMEType $mimeType,
        String $description,
        String $copyrightHolder
    ) {
        $this->fileId = $fileId;
        $this->description = $description;
        $this->copyrightHolder = $copyrightHolder;
        $this->mimeType = $mimeType;
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
}
