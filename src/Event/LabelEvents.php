<?php


namespace CultuurNet\UDB3\Event;

use CultuurNet\Entry\Label;

class LabelEvents
{
    /**
     * @var array
     */
    protected $eventIds;

    /**
     * @var Label
     */
    protected $label;

    public function __construct($eventIds, Label $label)
    {
        $this->eventIds = $eventIds;
        $this->label = $label;
    }

    public function getEventIds()
    {
        return $this->eventIds;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }
}
