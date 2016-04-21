<?php

namespace CultuurNet\UDB3\Offer\Events;

abstract class AbstractTypicalAgeRangeUpdated extends AbstractEvent
{
    /**
     * The new typical age range.
     * @var string
     */
    protected $typicalAgeRange;

    /**
     * @param string $id
     * @param string $typicalAgeRange
     */
    public function __construct($id, $typicalAgeRange)
    {
        parent::__construct($id);
        $this->typicalAgeRange = $typicalAgeRange;
    }

    /**
     * @return string
     */
    public function getTypicalAgeRange()
    {
        return $this->typicalAgeRange;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'typicalAgeRange' => $this->typicalAgeRange,
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], $data['typicalAgeRange']);
    }
}
