<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


class Title implements \JsonSerializable
{
    private $value;

    public function __construct($title)
    {
        if (!is_string($title)) {
            throw new \InvalidArgumentException('Title must be a string.');
        }

        $title = trim($title);

        if ($title == '') {
            throw new \InvalidArgumentException('Title can not be empty.');
        }

        $this->value = $title;
    }

    function __toString()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    function jsonSerialize()
    {
        return (string) $this;
    }


}
