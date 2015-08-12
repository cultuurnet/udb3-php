<?php

namespace CultuurNet\UDB3\Variations;

use Exception;
use RuntimeException;

/**
 * Exception thrown when an aggregate is found but deleted.
 */
class AggregateDeletedException extends RuntimeException
{
    /**
     * @param mixed     $id
     * @param Exception $previous
     *
     * @return AggregateDeletedException
     */
    public static function create($id, Exception $previous = null)
    {
        return new self(sprintf("Aggregate with id '%s' was deleted", $id), 0, $previous);
    }
}
