<?php

namespace CultuurNet\UDB3\Security;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Offer\Commands\PreflightCommand;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\StringLiteral\StringLiteral;

class CommandAuthorizationExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StringLiteral
     */
    private $userId;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @var string
     */
    private $itemId;

    /**
     * @var AuthorizableCommandInterface
     */
    private $command;

    /**
     * @var CommandAuthorizationException
     */
    private $commandAuthorizationException;

    protected function setUp()
    {
        $this->userId = new StringLiteral('85b040e5-766a-4ca7-a01b-e21e9250165f');
        $this->permission = Permission::AANBOD_BEWERKEN();
        $this->itemId = '69aa5d8d-5d56-4774-9320-d8e7c1721693';
        $this->command = new PreflightCommand($this->itemId, $this->permission);

        $this->commandAuthorizationException = new CommandAuthorizationException(
            $this->userId,
            $this->command
        );
    }

    /**
     * @test
     */
    public function it_stores_a_user_id()
    {
        $this->assertEquals(
            $this->userId,
            $this->commandAuthorizationException->getUserId()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_command()
    {
        $this->assertEquals(
            $this->command,
            $this->commandAuthorizationException->getCommand()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_message()
    {
        $expectedMessage = 'User with id: ' . $this->userId->toNative() .
            ' has no permission: "' . $this->permission->toNative() .
            '" on item: ' . $this->itemId .
            ' when executing command: ' . get_class($this->command);

        $this->assertEquals(
            $expectedMessage,
            $this->commandAuthorizationException->getMessage()
        );
    }
}
