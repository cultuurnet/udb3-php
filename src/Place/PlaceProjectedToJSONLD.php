<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place;

class PlaceProjectedToJSONLD
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
