<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations;

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
     * @var Purpose
     */
    private $purpose;

    /**
     * @param string $eventId
     * @param string $originalEventId
     * @param string $ownerId
     * @param Purpose $purpose
     */
    public function __construct($eventId, $originalEventId, $ownerId, Purpose $purpose)
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
     * @return Purpose
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * @param Purpose $purpose
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'original_event_id' => $this->getOriginalEventId(),
            'owner_id' => $this->getOwnerId(),
            'purpose' => (string) $this->getPurpose()
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
            $data['owner_id'],
            new Purpose($data['purpose'])
        );
    }
}
