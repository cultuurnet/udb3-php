<?php

namespace CultuurNet\UDB3\MyOrganizers;

use ValueObjects\Number\Natural;

interface MyOrganizersLookupServiceInterface
{
    public function itemsOwnedByUser(
        string $userId,
        Natural $limit,
        Natural $start
    ): PartOfCollection;
}
