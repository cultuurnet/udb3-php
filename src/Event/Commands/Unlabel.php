<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Label;

class Unlabel
{
    /**
     * @var string
     */
    protected $eventId;

    /**
     * @var Label
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
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }
}
