<?php


namespace CultuurNet\UDB3\Event;

class TagEvents
{
    /**
     * @var array
     */
    protected $eventIds;

    /**
     * @var string
     */
    protected $keyword;

    public function __construct($eventIds, $keyword)
    {
        $this->eventIds = $eventIds;
        $this->keyword = $keyword;
    }

    public function getEventIds()
    {
        return $this->eventIds;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

} 
