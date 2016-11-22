<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelEventInterface;

abstract class AbstractLabelEvent extends AbstractEvent implements LabelEventInterface
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
            'visibility' => $this->label->isVisible(),
        );
    }

    /**
     * @param array $data
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        if (!isset($data['visibility'])) {
            $data['visibility'] = true;
        }

        return new static(
            $data['item_id'],
            new Label($data['label'], $data['visibility'])
        );
    }
}
