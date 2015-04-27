<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedLabelsMemory;

use CultuurNet\UDB3\Label;

class LabelUsed extends Event
{
    /**
     * @var Label
     */
    protected $label;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $userId
     * @param Label $label
     */
    public function __construct($userId, Label $label)
    {
        $this->userId = $userId;
        $this->label = $label;
    }

    public static function deserialize(array $data)
    {
        $label = new Label($data['label']);
        // compatibility layer end
        return new static($data['user_id'], $label);
    }

    public function serialize()
    {
        return parent::serialize() + array(
            'label' => (string)$this->label,
        );
    }
}
