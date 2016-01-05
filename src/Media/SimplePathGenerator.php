<?php

namespace CultuurNet\UDB3\Media;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class SimplePathGenerator implements PathGeneratorInterface
{
    /**
     * @{inheritdoc}
     */
    public function path(UUID $fileId, String $extension)
    {
        return (string) $fileId . '.' . (string) $extension;
    }
}