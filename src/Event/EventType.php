<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

class EventType
{
    const DOMAIN = 'eventtype';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    public function __construct($id, $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
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
