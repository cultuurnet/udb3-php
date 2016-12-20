<?php

namespace CultuurNet\UDB3\Media;

use Broadway\CommandHandling\CommandHandlerInterface;
use CultuurNet\UDB3\Media\Commands\UploadImage;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Url;

interface MediaManagerInterface extends CommandHandlerInterface
{
    /**
     * @param UUID $id
     * @throws MediaObjectNotFoundException
     * @return MediaObject
     */
    public function get(UUID $id);

    /**
     * @param UploadImage $uploadImage
     * @return mixed
     */
    public function handleUploadImage(UploadImage $uploadImage);

    /**
     * @param UUID $id
     * @param MIMEType $mimeType
     * @param StringLiteral $description
     * @param StringLiteral $copyrightHolder
     * @param Url $sourceLocation
     *
     * @return MediaObject
     */
    public function create(
        UUID $id,
        MIMEType $mimeType,
        StringLiteral $description,
        StringLiteral $copyrightHolder,
        Url $sourceLocation
    );
}
