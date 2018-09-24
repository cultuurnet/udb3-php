<?php

namespace CultuurNet\UDB3\Cdb;

use CultureFeed_Cdb_Item_Base;

class ItemBaseAdapterFactory
{
    /**
     * @var
     */
    private $userIdResolver;

    public function __construct(CreatedByToUserIdResolverInterface $userIdResolver)
    {
        $this->userIdResolver = $userIdResolver;
    }

    public function create(CultureFeed_Cdb_Item_Base $item)
    {
        return new ItemBaseAdapter($item, $this->userIdResolver);
    }
}
