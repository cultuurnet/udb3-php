<?php

namespace CultuurNet\UDB3\Role\Commands;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class AbstractUserCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractUserCommand
     */
    private $abstractUserCommand;

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

        $this->abstractUserCommand = $this->getMock(
            AbstractUserCommand::class,
            null,
            [$this->uuid, $this->userId]
        );
    }

    /**
     * @test
     */
    public function it_stores_a_uuid()
    {
        $this->assertEquals($this->uuid, $this->abstractUserCommand->getUuid());
    }

    /**
     * @test
     */
    public function it_stores_a_user_id()
    {
        $this->assertEquals(
            $this->userId,
            $this->abstractUserCommand->getUserId()
        );
    }
}
