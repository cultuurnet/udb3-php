<?php

namespace CultuurNet\UDB3\Cdb;

use CultureFeed_Cdb_Item_Base;
use DateTimeImmutable;
use ValueObjects\StringLiteral\StringLiteral;

class ItemBaseAdapter
{
    /**
     * @var CultureFeed_Cdb_Item_Base
     */
    private $item;

    /**
     * @var CreatedByToUserIdResolverInterface
     */
    private $userIdResolver;

    public function __construct(
        CultureFeed_Cdb_Item_Base $item,
        CreatedByToUserIdResolverInterface $userIdResolver
    ) {
        $this->item = $item;
        $this->userIdResolver = $userIdResolver;
    }

    public function getResolvedCreatorUserId(): string
    {
        $createdByIdentifier = $this->item->getCreatedBy();
        if ($createdByIdentifier) {
            $userId = $this->userIdResolver->resolveCreatedByToUserId(
                new StringLiteral($createdByIdentifier)
            );
        }

        return isset($userId) ? (string) $userId : '';
    }

    public function getCreationDateTime(): ?DateTimeImmutable
    {
        return DateTimeFactory::dateTimeFromDateString(
            $this->item->getCreationDate()
        );
    }
}
