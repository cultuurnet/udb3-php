<?php

namespace CultuurNet\UDB3\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use ValueObjects\StringLiteral\StringLiteral;

/**
 * @todo Move to udb3-symfony
 * @see https://jira.uitdatabank.be/browse/III-1513
 */
interface ImageUploaderInterface
{
    /**
     * @param UploadedFile $file
     * @param StringLiteral $description
     * @param StringLiteral $copyrightHolder
     *
     * @return String
     *  The id of the upload command.
     */
    public function upload(UploadedFile $file, StringLiteral $description, StringLiteral $copyrightHolder);

    /**
     * @return string
     *  path to upload directory
     */
    public function getUploadDirectory();
}
