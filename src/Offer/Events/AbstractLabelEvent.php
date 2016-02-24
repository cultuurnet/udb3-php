<?php

namespace CultuurNet\UDB3\Offer\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Label;

abstract class AbstractLabelEvent extends AbstractEvent
{
    /**
     * @var Label
     */
    protected $label;

    /**
     * {@inheritdoc}
     *
     * @param Label $label
     *  The label that is involved in the event.
     */
    public function __construct($itemId, Label $label)
    {
        parent::__construct($itemId);
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
            'label' => (string) $this->label,
        );
    }

    /**
     * @param array $data
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            new Label($data['label'])
        );
    }
}
