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
     * @var string
     */
    private $ownerId;

    /**
     * @param string $eventId
     * @param string $originalEventId
     * @param string $ownerId
     */
    public function __construct($eventId, $originalEventId, $ownerId)
    {
        parent::__construct($eventId);

        $this->setOriginalEventId($originalEventId);
        $this->setOwnerId($ownerId);
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
     * @return string
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param string $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'original_event_id' => $this->getOriginalEventId(),
            'owner_id' => $this->getOwnerId()
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['event_id'],
            $data['original_event_id'],
            $data['owner_id']
        );
    }
}
