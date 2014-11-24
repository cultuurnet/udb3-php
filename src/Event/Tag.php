<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Keyword;

class Tag
{
    /**
     * @var string
     */
    protected $eventId;

    /**
     * @var Keyword
     */
    protected $keyword;

    public function __construct($eventId, Keyword $keyword)
    {
        $this->keyword = $keyword;
        $this->eventId = $eventId;
    }

    /**
     * @return string
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return Keyword
     */
    public function getKeyword()
    {
        return $this->keyword;
    }
}
