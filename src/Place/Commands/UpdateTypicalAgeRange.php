<?php

namespace CultuurNet\UDB3\Place\Commands;

class UpdateTypicalAgeRange
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $typicalAgeRange;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTypicalAgeRange()
    {
        return $this->typicalAgeRange;
    }

    /**
     * UpdateTypicalAgeRange constructor.
     * @param $id
     * @param $typicalAgeRange
     */
    public function __construct($id, $typicalAgeRange)
    {
        $this->id = $id;
        $this->typicalAgeRange = $typicalAgeRange;
    }
}
