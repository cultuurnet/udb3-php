<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\LabelsImportedEventInterface;
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Label\Label;
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Label\LabelName;
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Label\Labels;

class LabelsImported extends OrganizerEvent implements LabelsImportedEventInterface
{
    /**
     * @var Labels
     */
    private $labels;

    /**
     * @param string $organizerId
     * @param Labels $labels
     */
    public function __construct(
        $organizerId,
        Labels $labels
    ) {
        parent::__construct($organizerId);
        $this->labels = $labels;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->getOrganizerId();
    }

    /**
     * @return Labels
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $labels = new Labels();
        foreach ($data['labels'] as $label) {
            $labels = $labels->with(new Label(
                new LabelName($label['label']),
                $label['visibility']
            ));
        }

        return new static(
            $data['organizer_id'],
            $labels
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $labels = [];
        foreach ($this->getLabels() as $label) {
            /** @var Label $label */
            $labels[] = [
                'label' => $label->getName()->toString(),
                'visibility' => $label->isVisible(),
            ];
        }

        return parent::serialize() + [
            'labels' => $labels,
        ];
    }
}
