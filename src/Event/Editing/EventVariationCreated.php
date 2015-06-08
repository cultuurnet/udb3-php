<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Editing;

use CultuurNet\UDB3\Event\EventEvent;

class EventVariationCreated extends EventEvent
{
    /**
     * @var string
     */
    private $originalEventId;

    /**
     * @param string $eventId
     * @param string $originalEventId
     */
    public function __construct($eventId, $originalEventId)
    {
        parent::__construct($eventId);

        $this->setOriginalEventId($originalEventId);
    }

    /**
     * @return string
     */
    public function getOriginalEventId()
    {
        return $this->originalEventId;
    }

    /**
     * @param string $originalEventId
     */
    public function setOriginalEventId($originalEventId)
    {
        $this->originalEventId = $originalEventId;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'original_event_id' => $this->getOriginalEventId()
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['event_id'],
            $data['original_event_id']
        );
    }
}
