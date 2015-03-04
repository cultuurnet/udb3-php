<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\History;

use ValueObjects\String\String;

class Log implements \JsonSerializable
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var String
     */
    private $author;

    /**
     * @var String
     */
    private $description;

    public function __construct(
        \DateTime $date,
        String $description,
        String $author = null
    ) {
        $this->date = clone $date;
        $this->description = $description;
        $this->author = $author;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $log = [
            'date' => $this->date->format('c'),
            'description' => $this->description->toNative(),
        ];

        if ($this->author) {
            $log['author'] = $this->author->toNative();
        }

        return $log;
    }
}
