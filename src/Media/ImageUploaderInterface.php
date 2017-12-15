<?php

namespace CultuurNet\UDB3\Media;

use CultuurNet\UDB3\Language;
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
     * @param Language $language
     * @return ImageUploadResult
     */
    public function upload(
        UploadedFile $file,
        StringLiteral $description,
        StringLiteral $copyrightHolder,
        Language $language
    );

    /**
     * @return string
     *  path to upload directory
     */
    public function getUploadDirectory();
}
