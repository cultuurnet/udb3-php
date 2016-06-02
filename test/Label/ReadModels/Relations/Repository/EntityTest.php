<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var RelationType
     */
    private $relationType;

    /**
     * @var StringLiteral
     */
    private $relationId;

    /**
     * @var Entity
     */
    private $entity;

    protected function setUp()
    {
        $this->uuid = new UUID();
        $this->relationType = RelationType::PLACE();
        $this->relationId = new StringLiteral('relationId');

        $this->entity = new Entity(
            $this->uuid,
            $this->relationType,
            $this->relationId
        );
    }

    /**
     * @test
     */
    public function it_stores_a_uuid()
    {
        $this->assertEquals($this->uuid, $this->entity->getUuid());
    }

    /**
     * @test
     */
    public function it_stores_a_relation_type()
    {
        $this->assertEquals(
            $this->relationType,
            $this->entity->getRelationType()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_relation_id()
    {
        $this->assertEquals($this->relationId, $this->entity->getRelationId());
    }
}
