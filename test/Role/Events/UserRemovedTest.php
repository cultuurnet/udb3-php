<?php

namespace CultuurNet\UDB3\Role\Events;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class UserRemovedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserRemoved
     */
    private $userRemoved;

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

        $this->userRemoved = new UserRemoved($this->uuid, $this->userId);
    }

    /**
     * @test
     */
    public function it_extends_an_abstract_user_event()
    {
        $this->assertTrue(is_subclass_of(
            $this->userRemoved,
            AbstractUserEvent::class
        ));
    }

    /**
     * @test
     */
    public function it_can_deserialize()
    {
        $userRemovedAsArray = [
            AbstractUserEvent::UUID => $this->uuid->toNative(),
            AbstractUserEvent::USER_ID => $this->userId->toNative()
        ];

        $actualUserAdded = UserAdded::deserialize($userRemovedAsArray);

        $this->assertEquals($this->userRemoved, $actualUserAdded);
    }
}
