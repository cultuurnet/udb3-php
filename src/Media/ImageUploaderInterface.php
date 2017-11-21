<?php

namespace CultuurNet\UDB3\Media;

use CultuurNet\UDB3\Language;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

/**
 * @todo Move to udb3-symfony
 * @see https://jira.uitdatabank.be/browse/III-1513
 */
interface ImageUploaderInterface
{
    /**
     * Upload an image synchronous and get uuid of the uploaded image.
     *
     * @param UploadedFile $file
     * @param StringLiteral $description
     * @param StringLiteral $copyrightHolder
     * @param Language $language
     *
     * @return UUID
     *  The uuid of the uploaded image.
     */
    public function upload(
        UploadedFile $file,
        StringLiteral $description,
        StringLiteral $copyrightHolder,
        Language $language
    );

    /**
     * Upload an image asynchronous and get id of the command.
     *
     * @param UploadedFile $file
     * @param StringLiteral $description
     * @param StringLiteral $copyrightHolder
     * @param Language $language
     *
     * @return String
     *  The id of the upload command.
     */
    public function uploadAsync(
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
