<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\Number\Integer as IntegerValue;
use ValueObjects\String\String as StringLiteral;

class DBALWriteRepository extends AbstractDBALRepository implements WriteRepositoryInterface
{
    /**
     * @param UUID $uuid
     * @param StringLiteral $name
     * @param Visibility $visibility
     * @param Privacy $privacy
     * @param UUID|null $parentUuid
     */
    public function save(
        UUID $uuid,
        StringLiteral $name,
        Visibility $visibility,
        Privacy $privacy,
        UUID $parentUuid = null
    ) {
        $this->getQueryBuilder()->insert($this->getTableName())
            ->values([
                SchemaConfigurator::UUID_COLUMN => '?',
                SchemaConfigurator::NAME_COLUMN => '?',
                SchemaConfigurator::VISIBLE_COLUMN => '?',
                SchemaConfigurator::PRIVATE_COLUMN => '?',
                SchemaConfigurator::PARENT_UUID_COLUMN => '?'
            ])
            ->setParameters([
                $uuid->toNative(),
                $name->toNative(),
                $visibility === Visibility::VISIBLE() ? true : false,
                $privacy === Privacy::PRIVACY_PRIVATE() ? true : false,
                $parentUuid ? $parentUuid->toNative() : null
            ]);

        $this->getQueryBuilder()->execute();
    }

    /**
     * @param UUID $uuid
     */
    public function updateVisible(UUID $uuid)
    {
        $this->executeUpdate(
            SchemaConfigurator::VISIBLE_COLUMN,
            true,
            $uuid
        );
    }

    /**
     * @param UUID $uuid
     */
    public function updateInvisible(UUID $uuid)
    {
        $this->executeUpdate(
            SchemaConfigurator::VISIBLE_COLUMN,
            false,
            $uuid
        );
    }

    /**
     * @param UUID $uuid
     */
    public function updatePublic(UUID $uuid)
    {
        $this->executeUpdate(
            SchemaConfigurator::PRIVATE_COLUMN,
            false,
            $uuid
        );
    }

    /**
     * @param UUID $uuid
     */
    public function updatePrivate(UUID $uuid)
    {
        $this->executeUpdate(
            SchemaConfigurator::PRIVATE_COLUMN,
            true,
            $uuid
        );
    }

    /**
     * @param UUID $uuid
     */
    public function updateCountIncrement(UUID $uuid)
    {
        $this->executeCountUpdate(
            new IntegerValue(+1),
            $uuid
        );
    }

    /**
     * @param UUID $uuid
     */
    public function updateCountDecrement(UUID $uuid)
    {
        $this->executeCountUpdate(
            new IntegerValue(-1),
            $uuid
        );
    }

    /**
     * @param $column
     * @param $value
     * @param UUID $uuid
     */
    private function executeUpdate(
        $column,
        $value,
        UUID $uuid
    ) {
        $this->getQueryBuilder()->update($this->getTableName())
            ->set($column, '?')
            ->where(SchemaConfigurator::UUID_COLUMN . ' = ?')
            ->setParameters([
                $value,
                $uuid->toNative()
            ]);

        $this->getQueryBuilder()->execute();
    }

    /**
     * @param IntegerValue $value
     * @param UUID $uuid
     */
    private function executeCountUpdate(
        IntegerValue $value,
        UUID $uuid
    ) {
        $this->getQueryBuilder()->update($this->getTableName())
            ->set(
                SchemaConfigurator::COUNT_COLUMN,
                SchemaConfigurator::COUNT_COLUMN . ' + ' . $value->toNative()
            )
            ->where(SchemaConfigurator::UUID_COLUMN . ' = ?')
            ->setParameters([
                $uuid->toNative()
            ]);

        $this->getQueryBuilder()->execute();
    }
}
