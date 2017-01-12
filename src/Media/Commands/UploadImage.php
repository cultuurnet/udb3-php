<?php

namespace CultuurNet\UDB3\Media\Commands;

use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class UploadImage
{
    /**
     * @var UUID
     */
    protected $fileId;

    /**
     * @var StringLiteral
     */
    protected $description;

    /**
     * @var StringLiteral
     */
    protected $copyrightHolder;

    /**
     * @var MIMEType
     */
    protected $mimeType;

    /**
     * @var StringLiteral
     */
    protected $filePath;
    /**
     * @param UUID $fileId
     * @param MIMEType $mimeType
     * @param StringLiteral $description
     * @param StringLiteral $copyrightHolder
     * @param StringLiteral $filePath
     */
    public function __construct(
        UUID $fileId,
        MIMEType $mimeType,
        StringLiteral $description,
        StringLiteral $copyrightHolder,
        StringLiteral $filePath
    ) {
        $this->fileId = $fileId;
        $this->description = $description;
        $this->copyrightHolder = $copyrightHolder;
        $this->mimeType = $mimeType;
        $this->filePath = $filePath;
    }

    /**
     * @return UUID
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @return StringLiteral
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return StringLiteral
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
     * @return StringLiteral
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
