<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;

abstract class Offer extends EventSourcedAggregateRoot
{
    /**
     * @var LabelCollection
     */
    protected $labels;

    /**
     * @return LabelCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param Label $label
     */
    public function addLabel(Label $label)
    {
        if (!$this->labels->contains($label)) {
            $this->apply(
                $this->createLabelAddedEvent($label)
            );
        }
    }

    /**
     * @param Label $label
     */
    public function deleteLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->apply(
                $this->createLabelDeletedEvent($label)
            );
        }
    }

    /**
     * @param AbstractLabelAdded $labelAdded
     */
    protected function applyLabelAdded(AbstractLabelAdded $labelAdded)
    {
        $newLabel = $labelAdded->getLabel();

        if (!$this->labels->contains($newLabel)) {
            $this->labels = $this->labels->with($newLabel);
        }
    }

    /**
     * @param AbstractLabelDeleted $labelDeleted
     */
    protected function applyLabelDeleted(AbstractLabelDeleted $labelDeleted)
    {
        $this->labels = $this->labels->without(
            $labelDeleted->getLabel()
        );
    }

    /**
     * @param Label $label
     * @return AbstractLabelAdded
     */
    abstract protected function createLabelAddedEvent(Label $label);

    /**
     * @param Label $label
     * @return AbstractLabelDeleted
     */
    abstract protected function createLabelDeletedEvent(Label $label);
}
