<?php

namespace CultuurNet\UDB3\Organizer\ReadModel\Search;

use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Url;

class Query
{
    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * @var Url
     */
    private $website;

    /**
     * @var Natural
     */
    private $offset;

    /**
     * @var Natural
     */
    private $limit;

    /**
     * Query constructor.
     * @param Natural $offset
     * @param Natural $limit
     * @param Url|null $website
     * @param StringLiteral|null $name
     */
    public function __construct(
        Natural $offset,
        Natural $limit,
        Url $website = null,
        StringLiteral $name = null
    ) {
        $this->name = $name;
        $this->website = $website;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * @return StringLiteral|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Url|null
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return Natural
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return Natural
     */
    public function getLimit()
    {
        return $this->limit;
    }
}
