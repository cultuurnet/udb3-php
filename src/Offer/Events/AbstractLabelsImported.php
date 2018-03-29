<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\LabelsImportedEventInterface;
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Label\Label;
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Label\LabelName;
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Label\Labels;

abstract class AbstractLabelsImported extends AbstractEvent implements LabelsImportedEventInterface
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
            $data['item_id'],
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
