<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Command;

/**
 * Exception to indicate that validation failed.
 */
class ValidationException extends \RuntimeException
{
    /**
     * @var array
     */
    private $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Invalid data.');
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
