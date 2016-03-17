<?php

namespace CultuurNet\UDB3\Dashboard;

use CultuurNet\UDB3\Search\Results;
use CultuurNet\UiTIDProvider\User\User;
use ValueObjects\Number\Natural;
use ValueObjects\Web\Domain;

interface DashboardItemLookupServiceInterface
{
    /**
     * @param Natural $limit
     *   How many items to retrieve.
     * @param Natural $start
     *   Offset to start from.
     * @param User $user
     *  The user to find dashboard items for.
     *
     * @return Results
     */
    public function findByUser(
        User $user,
        Natural $limit,
        Natural $start
    );

    /**
     * @param Natural $limit
     *   How many items to retrieve.
     * @param Natural $start
     *   Offset to start from.
     * @param User $user
     *  The user to find dashboard items for.
     * @param Domain $domain
     *  The domain that owns the items.
     *
     * @return Results
     */
    public function findByUserForDomain(
        User $user,
        Domain $domain,
        Natural $limit,
        Natural $start
    );
}
