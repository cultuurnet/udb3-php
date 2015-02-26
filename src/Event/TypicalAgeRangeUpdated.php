<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\TypicalAgeRangeUpdated.
 */

namespace CultuurNet\UDB3\Event;

/**
 * Description of TypicalAgeRangeUpdated
 */
class TypicalAgeRangeUpdated  extends EventEvent
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

}
