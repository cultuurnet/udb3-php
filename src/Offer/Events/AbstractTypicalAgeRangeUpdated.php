<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Offer\AgeRange;

abstract class AbstractTypicalAgeRangeUpdated extends AbstractEvent
{
    /**
     * The new typical age range.
     * @var AgeRange
     */
    protected $typicalAgeRange;

    /**
     * @param string $id
     * @param AgeRange $typicalAgeRange
     */
    final public function __construct(string $id, AgeRange $typicalAgeRange)
    {
        parent::__construct($id);
        $this->typicalAgeRange = $typicalAgeRange;
    }

    /**
     * @return AgeRange
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
            'typicalAgeRange' => (string) $this->typicalAgeRange,
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], AgeRange::fromString($data['typicalAgeRange']));
    }
}
