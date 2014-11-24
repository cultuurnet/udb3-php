<?php


namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Keyword;

class TagQuery
{
    /**
     * @var string
     */
    protected $query;

    /**
     * @var Keyword
     */
    protected $keyword;

    public function __construct($query, Keyword $keyword)
    {
        $this->query = $query;
        $this->keyword = $keyword;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return Keyword
     */
    public function getKeyword()
    {
        return $this->keyword;
    }
}
