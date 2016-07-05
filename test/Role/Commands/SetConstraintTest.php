<?php

namespace CultuurNet\UDB3\Role\Commands;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as stringLiteral;

class SetConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    protected $uuid;

    /**
     * @var StringLiteral
     */
    protected $query;

    /**
     * @var SetConstraint
     */
    protected $setConstraint;

    protected function setUp()
    {
        $this->uuid = new UUID();

        $this->query = new StringLiteral('category_falndersregion_name:"Regio Aalst"');

        $this->setConstraint = new SetConstraint(
            $this->uuid,
            $this->query
        );
    }

    /**
     * @test
     */
    public function it_extends_an_abstract_command()
    {
        $this->assertTrue(is_subclass_of(
            $this->setConstraint,
            AbstractCommand::class
        ));
    }

    /**
     * @test
     */
    public function it_stores_a_uuid()
    {
        $this->assertEquals($this->uuid, $this->setConstraint->getUuid());
    }

    /**
     * @test
     */
    public function it_stores_a_query()
    {
        $this->assertEquals($this->query, $this->setConstraint->getQuery());
    }

    /**
     * @test
     */
    public function it_can_serialize()
    {
        $actualCreate = unserialize(serialize($this->setConstraint));

        $this->assertEquals($this->setConstraint, $actualCreate);
    }
}
