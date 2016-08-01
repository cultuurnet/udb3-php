<?php

namespace CultuurNet\UDB3\Role\Commands;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class SetConstraint extends AbstractCommand
{
    /**
     * @var StringLiteral
     */
    private $query;

    public function __construct(
        UUID $uuid,
        StringLiteral $query
    ) {
        parent::__construct($uuid);

        $this->query = $query;
    }

    /**
     * @return StringLiteral
     */
    public function getQuery()
    {
        return $this->query;
    }
}
