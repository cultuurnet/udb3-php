<?php

namespace CultuurNet\UDB3\Dashboard;

use CultuurNet\UDB3\Search\Results;
use CultuurNet\UiTIDProvider\User\User;
use ValueObjects\Web\Domain;

interface DashboardItemLookupServiceInterface
{
    /**
     * @param int $limit
     *   How many items to retrieve.
     * @param int $start
     *   Offset to start from.
     * @param User $user
     *  The user to find dashboard items for.
     *
     * @return Results
     */
    public function findByUser(User $user, $limit = 50, $start = 0);

    /**
     * @param int $limit
     *   How many items to retrieve.
     * @param int $start
     *   Offset to start from.
     * @param User $user
     *  The user to find dashboard items for.
     * @param Domain $domain
     *  The domain that owns the items.
     *
     * @return Results
     */
    public function findByUserForDomain(User $user, Domain $domain, $limit = 50, $start = 0);
}