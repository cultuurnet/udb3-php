<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedLabelsMemory;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Label;

class UsedLabelsMemory extends EventSourcedAggregateRoot implements \JsonSerializable
{
    protected $userId;

    /**
     * @var Label[]
     */
    protected $usedLabels;

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->userId;
    }

    /**
     * @return Label[]
     */
    public function getLabels()
    {
        return array_values($this->usedLabels);
    }

    public function __construct()
    {
        $this->usedLabels = array();
    }

    /**
     * Remember a label was used.
     *
     * @param Label $label
     */
    public function labelUsed(Label $label)
    {
        $lastUsedLabel = reset($this->usedLabels);

        if ((string)$label !== (string)$lastUsedLabel) {
            $this->apply(new LabelUsed($this->userId, $label));
        }
    }

    protected function shrinkToMaximumSize()
    {
        while (count($this->usedLabels) > 10) {
            array_pop($this->usedLabels);
        }
    }

    /**
     * @param LabelUsed $labelUsed
     */
    protected function applyLabelUsed(LabelUsed $labelUsed)
    {
        $key = array_search($labelUsed->getLabel(), $this->usedLabels);
        if (false !== $key) {
            unset($this->usedLabels[$key]);
        }
        array_unshift($this->usedLabels, $labelUsed->getLabel());

        $this->shrinkToMaximumSize();
    }

    /**
     * @param string $userId
     * @return static
     */
    public static function create($userId)
    {
        $usedLabelsMemory = new static();
        $usedLabelsMemory->apply(new Created($userId));

        return $usedLabelsMemory;
    }

    protected function applyCreated(Created $created)
    {
        $this->userId = $created->getUserId();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->usedLabels;
    }
}
