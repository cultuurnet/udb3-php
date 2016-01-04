<?php

namespace CultuurNet\UDB3\Media;

use Broadway\CommandHandling\CommandHandlerInterface;
use CultuurNet\UDB3\Media\Commands\UploadImage;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

interface MediaManagerInterface extends CommandHandlerInterface
{
    /**
     * @param UUID $fileId
     * @throws MediaObjectNotFoundException
     * @return MediaObject
     */
    public function get(UUID $fileId);

    /**
     * @param UploadImage $uploadImage
     * @return mixed
     */
    public function handleUploadImage(UploadImage $uploadImage);

    /**
     * @param UUID $fileId
     * @param MIMEType $mimeType
     * @param \ValueObjects\String\String $description
     * @param \ValueObjects\String\String $copyrightHolder
     *
     * @return MediaObject
     */
    public function create(
        UUID $fileId,
        MIMEType $mimeType,
        String $description,
        String $copyrightHolder,
        String $extension
    );
}
