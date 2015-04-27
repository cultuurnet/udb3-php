<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

class EventType extends Category
{
    const DOMAIN = 'eventtype';


    public function __construct($id, $label)
    {
        parent::__construct($id, $label, self::DOMAIN);
    }

    /**
     * Creates a new EventType object from a JSON-LD encoded event.
     *
     * @param string $eventString
     *   The cultural event encoded as JSON-LD
     *
     * @return self|null
     */
    public static function fromJSONLDEvent($eventString)
    {
        $event = json_decode($eventString);
        foreach ($event->terms as $term) {
            if ($term->domain == self::DOMAIN) {
                return new self($term->id, $term->label);
            }
        }
        return null;
    }
}
