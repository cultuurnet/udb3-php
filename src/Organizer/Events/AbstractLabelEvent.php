<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelEventInterface;

abstract class AbstractLabelEvent extends OrganizerEvent implements LabelEventInterface
{
    /**
     * @var Label
     */
    private $label;

    /**
     * @param string $organizerId
     * @param Label $label
     */
    final public function __construct(
        string $organizerId,
        Label $label
    ) {
        parent::__construct($organizerId);
        $this->label = $label;
    }

    /**
     * @inheritdoc
     */
    public function getItemId()
    {
        return $this->getOrganizerId();
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
