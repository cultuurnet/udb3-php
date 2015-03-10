<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Label;

class LabelCommand
{
    /**
     * @var string
     */
    protected $eventId;

    /**
     * @var LabelCommand
     */
    protected $label;

    public function __construct($eventId, Label $label)
    {
        $this->label = $label;
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
     * @return LabelCommand
     */
    public function getLabel()
    {
        return $this->label;
    }
}
