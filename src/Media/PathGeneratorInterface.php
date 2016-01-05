<?php

namespace CultuurNet\UDB3\Media;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

interface PathGeneratorInterface
{
    /**
     * Returns the path where a file is stored
     *
     * @param UUID $fileId
     * @param String $extension
     *
     * @return string
     */
    public function path(UUID $fileId, String $extension);
}