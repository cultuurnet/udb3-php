<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\Label;

/**
 * Interface for an event labeller service.
 */
interface EventLabellerServiceInterface
{
    /**
     * @param string[] $eventIds
     * @param Label $label
     * @return string command id
     * @throws EventNotFoundException
     * @throws \InvalidArgumentException
     */
    public function labelEventsById($eventIds, Label $label);

    /**
     * @param $query
     * @param Label $label
     * @return string The id of the command that's doing the labelling.
     */
    public function labelQuery($query, Label $label);
}
