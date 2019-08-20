<?php

namespace CultuurNet\UDB3\Place;

use Exception;

class CannotMarkPlaceAsDuplicate extends Exception
{
    public static function becauseItIsDeleted(string $placeId): self
    {
        return new static('Cannot mark place ' . $placeId . ' as duplicate because it is deleted');
    }
}
