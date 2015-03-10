<?php


namespace CultuurNet\UDB3\Event;

use CultuurNet\Entry\Keyword;

class LabelEvents
{
    /**
     * @var array
     */
    protected $eventIds;

    /**
     * @var Keyword
     */
    protected $label;

    public function __construct($eventIds, Keyword $label)
    {
        $this->eventIds = $eventIds;
        $this->label = $label;
    }

    public function getEventIds()
    {
        return $this->eventIds;
    }

    /**
     * @return Keyword
     */
    public function getLabel()
    {
        return $this->label;
    }
}
