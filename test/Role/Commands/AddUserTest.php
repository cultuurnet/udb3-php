<?php

namespace CultuurNet\UDB3\Role\Commands;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class AddUserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddUser
     */
    private $addUser;

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var StringLiteral
     */
    private $userId;

    protected function setUp()
    {
        $this->uuid = new UUID();

        $this->userId = new StringLiteral('userId');

        $this->addUser = new AddUser($this->uuid, $this->userId);
    }

    /**
     * @test
     */
    public function it_extends_an_abstract_user_command()
    {
        $this->assertTrue(is_subclass_of(
            $this->addUser,
            AbstractCommand::class
        ));
    }
}
