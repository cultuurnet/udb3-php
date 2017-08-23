<?php

namespace CultuurNet\UDB3;

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
    public function jsonSerialize()
    {
        return (string)$this;
    }
}
