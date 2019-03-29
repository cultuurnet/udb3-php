<?php

namespace CultuurNet\UDB3\Search\Narrowing;

interface QueryNarrowerInterface
{
    public function narrow(string $query): string;
}
