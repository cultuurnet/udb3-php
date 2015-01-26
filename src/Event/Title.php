<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


use CultuurNet\UDB3\TrimmedString;

class Title extends TrimmedString implements \JsonSerializable
{
    public function __construct($value)
    {
        parent::__construct($value);

        if ($this->isEmpty()) {
            throw new \InvalidArgumentException('Title can not be empty.');
        }
    }

    /**
     * @inheritdoc
     */
    function jsonSerialize()
    {
        return (string)$this;
    }
}
