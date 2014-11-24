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

        $value = trim($value);
        if ('' === $value) {
            throw new \InvalidArgumentException('Keyword should consist of at least one character');
        }

        if (false !== strpos($value, ';')) {
            throw new \InvalidArgumentException('Keyword should not contain semicolons');
        }
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}
