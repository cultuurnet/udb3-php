<?php


namespace CultuurNet\UDB3\Event;

class EventWasTagged extends EventEvent
{
    protected $keyword;

    public function __construct($eventId, $keyword)
    {
        parent::__construct($eventId);
        $this->keyword = $keyword;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }
}
