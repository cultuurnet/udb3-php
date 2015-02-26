<?php

/**
 * @file
 * Contains CultuurNet\UDB3\TypicalAgeRangeUpdatedTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Provides a trait for typical age range update events.
 */
trait TypicalAgeRangeUpdatedTrait
{

    /**
     * The new typical age range.
     * @var string
     */
    protected $typicalAgeRange;

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

}
