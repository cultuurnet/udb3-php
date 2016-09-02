<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository;

use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;

class Query
{
    /**
     * @var StringLiteral
     */
    private $value;

    /**
     * @var StringLiteral
     */
    private $userId;

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
     * @param StringLiteral $value
     * @param StringLiteral $userId
     * @param Natural $offset
     * @param Natural $limit
     */
    public function __construct(
        StringLiteral $value,
        StringLiteral $userId = null,
        Natural $offset = null,
        Natural $limit = null
    ) {
        $this->value = $value;
        $this->userId = $userId;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * @return StringLiteral
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return StringLiteral
     */
    public function getUserId()
    {
        return $this->userId;
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
