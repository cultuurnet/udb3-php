<?php

namespace CultuurNet\UDB3\Label\Services;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class WriteResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StringLiteral
     */
    private $commandId;

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var WriteResult
     */
    private $writeResult;

    protected function setUp()
    {
        $this->commandId = new StringLiteral('commandId');
        $this->uuid = new UUID();

        $this->writeResult = new WriteResult($this->commandId, $this->uuid);
    }

    /**
     * @test
     */
    public function it_stores_a_command_id()
    {
        $this->assertEquals(
            $this->commandId,
            $this->writeResult->getCommandId()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_uuid()
    {
        $this->assertEquals($this->uuid, $this->writeResult->getUuid());
    }

    /**
     * @test
     */
    public function it_has_a_default_uuid_of_null()
    {
        $writeResult = new WriteResult($this->commandId);

        $this->assertEquals($writeResult->getUuid(), null);
    }
}
