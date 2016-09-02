<?php

namespace CultuurNet\UDB3\Offer\Commands;

abstract class AbstractDeleteTypicalAgeRange
{
    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
