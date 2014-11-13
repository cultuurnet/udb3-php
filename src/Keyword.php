<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


class Keyword implements \JsonSerializable
{
    protected $value;

    public function __construct($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Keyword should be a string');
        }

        if ('' === $value) {
            throw new \InvalidArgumentException('Keyword should consist of at least one character');
        }

        if (FALSE !== strpos($value, ';')) {
            throw new \InvalidArgumentException('Keyword should not contain semicolons');
        }
        $this->value = $value;
    }

    function __toString()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return $this->value;
    }
} 
