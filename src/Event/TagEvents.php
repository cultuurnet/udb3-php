<?php


namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Keyword;

class TagEvents
{
    /**
     * @var array
     */
    protected $eventIds;

    /**
     * @var Keyword
     */
    protected $keyword;

    public function __construct($eventIds, Keyword $keyword)
    {
        $this->eventIds = $eventIds;
        $this->keyword = $keyword;
    }

    public function getEventIds()
    {
        return $this->eventIds;
    }

    /**
     * @return Keyword
     */
    public function getKeyword()
    {
        return $this->keyword;
    }
}
