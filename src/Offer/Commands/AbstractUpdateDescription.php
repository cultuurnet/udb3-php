<?php

namespace CultuurNet\UDB3\Offer\Commands;

abstract class AbstractUpdateDescription
{
    /**
     * Id that gets updated.
     * @var string
     */
    protected $id;

    /**
     * Description to be added.
     * @var string
     */
    protected $description;

    /**
     * @param string $id
     * @param string $description
     */
    public function __construct($id, $description)
    {
        $this->id = $id;
        $this->description = $description;
    }

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
    function getDescription()
    {
        return $this->description;
    }
}
