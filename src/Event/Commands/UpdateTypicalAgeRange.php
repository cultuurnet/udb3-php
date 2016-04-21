<?php

namespace CultuurNet\UDB3\Event\Commands;

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
     * @param string $id
     * @param string $typicalAgeRange
     */
    public function __construct($id, $typicalAgeRange)
    {
        $this->id = $id;
        $this->typicalAgeRange = $typicalAgeRange;
    }
}
