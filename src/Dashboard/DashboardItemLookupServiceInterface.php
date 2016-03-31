<?php

namespace CultuurNet\UDB3\Dashboard;

use CultureFeed_User;
use CultuurNet\UDB3\Search\Results;
use ValueObjects\Number\Natural;
use ValueObjects\Web\Domain;

interface DashboardItemLookupServiceInterface
{
    /**
     * @param Natural $limit
     *   How many items to retrieve.
     * @param Natural $start
     *   Offset to start from.
     * @param string $userId
     *  The user id to find dashboard items for.
     *
     * @return Results
     */
    public function findByUser(
        $userId,
        Natural $limit,
        Natural $start
    );

    /**
     * @param Natural $limit
     *   How many items to retrieve.
     * @param Natural $start
     *   Offset to start from.
     * @param string $userId
     *  The user id to find dashboard items for.
     * @param Domain $owningDomain
     *  The domain that owns the items.
     *
     * @return Results
     */
    public function findByUserForDomain(
        $userId,
        Natural $limit,
        Natural $start,
        Domain $owningDomain
    );
}
