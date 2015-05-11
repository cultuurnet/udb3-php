<?php


namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Label;

class LabelQuery
{
    /**
     * @var string
     */
    protected $query;

    /**
     * @var Label
     */
    protected $label;

    public function __construct($query, Label $label)
    {
        $this->query = $query;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }
}
