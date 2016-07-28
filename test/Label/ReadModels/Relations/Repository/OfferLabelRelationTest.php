<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class OfferLabelRelationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var OfferType
     */
    private $relationType;

    /**
     * @var StringLiteral
     */
    private $relationId;

    /**
     * @var OfferLabelRelation
     */
    private $offerLabelRelation;

    protected function setUp()
    {
        $this->uuid = new UUID();
        $this->relationType = OfferType::PLACE();
        $this->relationId = new StringLiteral('relationId');

        $this->offerLabelRelation = new OfferLabelRelation(
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
        $this->assertEquals($this->uuid, $this->offerLabelRelation->getUuid());
    }

    /**
     * @test
     */
    public function it_stores_a_relation_type()
    {
        $this->assertEquals(
            $this->relationType,
            $this->offerLabelRelation->getRelationType()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_relation_id()
    {
        $this->assertEquals($this->relationId, $this->offerLabelRelation->getRelationId());
    }

    /**
     * @test
     */
    public function it_can_encode_to_json()
    {
        $json = json_encode($this->offerLabelRelation);

        $expectedJson = '{"uuid":"' . $this->uuid->toNative()
            . '","relationType":"' . $this->relationType->toNative()
            . '","relationId":"' . $this->relationId->toNative() . '"}';

        $this->assertEquals($expectedJson, $json);
    }
}
