<?php

namespace CultuurNet\UDB3\Role\Commands;

use ValueObjects\Identity\UUID;

class AbstractCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var AbstractCommand
     */
    private $abstractCommand;

    protected function setUp()
    {
        $this->uuid = new UUID();

        $this->abstractCommand = $this->getMockForAbstractClass(
            AbstractCommand::class,
            [$this->uuid]
        );
    }

    /**
     * @test
     */
    public function it_stores_a_uuid()
    {
        $this->assertEquals($this->uuid, $this->abstractCommand->getUuid());
    }
}
