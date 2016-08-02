<?php

namespace CultuurNet\UDB3\Role;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Role\Commands\AddPermission;
use CultuurNet\UDB3\Role\Commands\AddUser;
use CultuurNet\UDB3\Role\Commands\CreateRole;
use CultuurNet\UDB3\Role\Commands\DeleteRole;
use CultuurNet\UDB3\Role\Commands\RemovePermission;
use CultuurNet\UDB3\Role\Commands\RemoveUser;
use CultuurNet\UDB3\Role\Commands\RenameRole;
use CultuurNet\UDB3\Role\Commands\SetConstraint;
use CultuurNet\UDB3\Role\Events\ConstraintCreated;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
use CultuurNet\UDB3\Role\Events\PermissionAdded;
use CultuurNet\UDB3\Role\Events\PermissionRemoved;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
use CultuurNet\UDB3\Role\Events\UserAdded;
use CultuurNet\UDB3\Role\Events\UserRemoved;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class CommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @var StringLiteral
     */
    private $query;

    /**
     * @var StringLiteral
     */
    private $updatedQuery;

    /**
     * @var RoleCreated
     */
    private $roleCreated;

    /**
     * @var RoleRenamed
     */
    private $roleRenamed;

    /**
     * @var PermissionAdded
     */
    private $permissionAdded;

    /**
     * @var PermissionRemoved
     */
    private $permissionRemoved;

    /**
     * @var ConstraintCreated
     */
    private $constraintCreated;

    /**
     * @var ConstraintUpdated
     */
    private $constraintUpdated;

    /**
     * @var ConstraintRemoved
     */
    private $constraintRemoved;

    /**
     * @var RoleDeleted
     */
    private $roleDeleted;

    public function setUp()
    {
        parent::setUp();

        $this->uuid = new UUID();
        $this->name = new StringLiteral('labelName');
        $this->permission = Permission::AANBOD_INVOEREN();
        $this->query = new StringLiteral('category_flandersregion_name:"Regio Aalst"');
        $this->updatedQuery = new StringLiteral('category_flandersregion_name:"Regio Brussel"');

        $this->roleCreated = new RoleCreated(
            $this->uuid,
            $this->name
        );

        $this->roleRenamed = new RoleRenamed(
            $this->uuid,
            $this->name
        );

        $this->permissionAdded = new PermissionAdded(
            $this->uuid,
            $this->permission
        );

        $this->permissionRemoved = new PermissionRemoved(
            $this->uuid,
            $this->permission
        );

        $this->constraintCreated = new ConstraintCreated(
            $this->uuid,
            $this->query
        );

        $this->constraintUpdated = new ConstraintUpdated(
            $this->uuid,
            $this->updatedQuery
        );

        $this->constraintRemoved = new ConstraintRemoved(
            $this->uuid
        );

        $this->roleDeleted = new RoleDeleted(
            $this->uuid
        );
    }

    /**
     * @inheritdoc
     */
    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        return new CommandHandler(new RoleRepository(
            $eventStore,
            $eventBus
        ));
    }

    /**
     * @test
     */
    public function it_handles_create()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([])
            ->when(new CreateRole(
                $this->uuid,
                $this->name
            ))
            ->then([$this->roleCreated]);
    }

    /**
     * @test
     */
    public function it_handles_rename()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(new RenameRole(
                $this->uuid,
                $this->name
            ))
            ->then([$this->roleRenamed]);
    }

    /**
     * @test
     */
    public function it_handles_addPermission()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(new AddPermission(
                $this->uuid,
                $this->permission
            ))
            ->then([$this->permissionAdded]);
    }

    /**
     * @test
     */
    public function it_handles_removePermission()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated, new PermissionAdded($this->uuid, $this->permission)])
            ->when(new RemovePermission(
                $this->uuid,
                $this->permission
            ))
            ->then([$this->permissionRemoved]);
    }

    /**
     * @test
     */
    public function it_can_add_and_remove_users()
    {
        $userId = new StringLiteral('123456');

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])

            // Add a user.
            ->when(
                new AddUser(
                    $this->uuid,
                    $userId
                )
            )
            ->then(
                [
                    new UserAdded(
                        $this->uuid,
                        $userId
                    ),
                ]
            )

            // Adding the same user should not result in any new events.
            ->when(
                new AddUser(
                    $this->uuid,
                    $userId
                )
            )
            ->then(
                [
                    new UserAdded(
                        $this->uuid,
                        $userId
                    ),
                ]
            )

            // Remove the user.
            ->when(
                new RemoveUser(
                    $this->uuid,
                    $userId
                )
            )
            ->then(
                [
                    new UserAdded(
                        $this->uuid,
                        $userId
                    ),
                    new UserRemoved(
                        $this->uuid,
                        $userId
                    ),
                ]
            )

            // Removing the user again should not result in any new events.
            ->when(
                new RemoveUser(
                    $this->uuid,
                    $userId
                )
            )
            ->then(
                [
                    new UserAdded(
                        $this->uuid,
                        $userId
                    ),
                    new UserRemoved(
                        $this->uuid,
                        $userId
                    ),
                ]
            )

            // Removing a user that was never added to the role should not
            // result in any new events.
            ->when(
                new RemoveUser(
                    $this->uuid,
                    new StringLiteral('user-that-was-never-added')
                )
            )
            ->then(
                [
                    new UserAdded(
                        $this->uuid,
                        $userId
                    ),
                    new UserRemoved(
                        $this->uuid,
                        $userId
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_handles_setConstraint_by_creating_the_constraint()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(new SetConstraint(
                $this->uuid,
                $this->query
            ))
            ->then([$this->constraintCreated]);
    }

    /**
     * @test
     */
    public function it_handles_setConstraint_by_updating_the_constraint()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated, $this->constraintCreated])
            ->when(new SetConstraint(
                $this->uuid,
                $this->updatedQuery
            ))
            ->then([$this->constraintUpdated]);
    }

    /**
     * @test
     */
    public function it_handles_setConstraint_by_removing_the_constraint()
    {
        $query = new StringLiteral('');

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated, $this->constraintCreated])
            ->when(new SetConstraint(
                $this->uuid,
                $query
            ))
            ->then([$this->constraintRemoved]);
    }

    /**
     * @test
     */
    public function it_handles_deleteRole_by_deleting_the_role()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(new DeleteRole(
                $this->uuid
            ))
            ->then([$this->roleDeleted]);
    }
}
