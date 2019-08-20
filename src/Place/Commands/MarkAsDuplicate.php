<?php

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;

final class MarkAsDuplicate implements AuthorizableCommandInterface
{
    /**
     * @var string
     */
    private $duplicatePlaceId;

    /**
     * @var string
     */
    private $masterPlaceId;

    public function __construct(string $duplicatePlaceId, string $masterPlaceId)
    {
        $this->duplicatePlaceId = $duplicatePlaceId;
        $this->masterPlaceId = $masterPlaceId;
    }

    public function getDuplicatePlaceId(): string
    {
        return $this->duplicatePlaceId;
    }

    public function getMasterPlaceId(): string
    {
        return $this->masterPlaceId;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->duplicatePlaceId;
    }

    /**
     * @return Permission
     */
    public function getPermission()
    {
        return Permission::GEBRUIKERS_BEHEREN();
    }
}
