<?php

/**
 * @file
 * Contains CultuurNet\UDB3\UpdateTypicalAgeRange.
 */

namespace CultuurNet\UDB3;

/**
 * Provides a trait for typicalAgeRange updates.
 */
trait UpdateTypicalAgeRangeTrait
{

    /**
     * @var string
     */
    protected $id;

    /**
     * The new typical age range.
     * @var string
     */
    protected $typicalAgeRange;

    /**
     * @return string
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    function getTypicalAgeRange()
    {
        return $this->typicalAgeRange;
    }
}
