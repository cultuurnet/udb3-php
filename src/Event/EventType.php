<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


class EventType
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    public function __construct($id, $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

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
    public function getLabel()
    {
        return $this->label;
    }

}
