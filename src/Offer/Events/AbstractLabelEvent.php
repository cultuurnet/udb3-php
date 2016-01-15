<?php

namespace CultuurNet\UDB3\Offer\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Label;

abstract class AbstractLabelEvent implements SerializableInterface
{
    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var Label
     */
    protected $label;

    public function __construct($itemId, Label $label)
    {
        $this->itemId = $itemId;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
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
        return array(
            'item_id' => $this->itemId,
            'label' => (string) $this->label,
        );
    }

    /**
     * @param array $data
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], new Label($data['label']));
    }
}
