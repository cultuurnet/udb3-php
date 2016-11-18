<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Label;

abstract class AbstractLabelEvent extends OrganizerEvent
{
    /**
     * @var Label
     */
    private $label;

    /**
     * @param string $organizerId
     * @param Label $label
     */
    public function __construct(
        $organizerId,
        Label $label
    ) {
        parent::__construct($organizerId);
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
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['organizer_id'],
            new Label(
                $data['label'],
                isset($data['visibility']) ? $data['visibility'] : true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + [
            'label' => (string) $this->label,
            'visibility' => $this->label->isVisible(),
        ];
    }
}
