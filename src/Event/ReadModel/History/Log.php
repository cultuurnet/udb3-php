<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\History;

use ValueObjects\StringLiteral\StringLiteral;

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

    /**
     * @var string
     */
    private $apiKey;

    public function __construct(
        \DateTime $date,
        StringLiteral $description,
        StringLiteral $author = null,
        string $apiKey = null
    ) {
        $this->date = clone $date;
        $this->description = $description;
        $this->author = $author;
        $this->apiKey = $apiKey;
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

        if ($this->apiKey) {
            $log['apiKey'] = $this->apiKey;
        }

        return $log;
    }
}
