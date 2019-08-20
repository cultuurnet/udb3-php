<?php

namespace CultuurNet\UDB3\Place;

use Exception;

class CannotMarkPlaceAsMaster extends Exception
{
    public static function becauseItIsDeleted(string $placeId): self
    {
        return new static('Cannot mark place ' . $placeId . ' as master because it is deleted');
    }
}
