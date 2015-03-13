<?php


namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Label;

class EventWasLabelled extends EventEvent
{
    /**
     * @var Label
     */
    protected $label;

    public function __construct($eventId, Label $label)
    {
        parent::__construct($eventId);
        $this->label = $label;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'label' => (string)$this->label,
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['event_id'], new Label($data['label']));
    }
}
