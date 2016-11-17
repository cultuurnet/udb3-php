<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class OfferLabelRelationTest extends \PHPUnit_Framework_TestCase
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
    private $offerId;

    /**
     * @var LabelRelation
     */
    private $offerLabelRelation;

    protected function setUp()
    {
        $this->uuid = new UUID();
        $this->relationType = RelationType::PLACE();
        $this->offerId = new StringLiteral('relationId');

        $this->offerLabelRelation = new LabelRelation(
            $this->uuid,
            $this->relationType,
            $this->offerId
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
        $this->assertEquals($this->offerId, $this->offerLabelRelation->getRelationId());
    }

    /**
     * @test
     */
    public function it_can_encode_to_json()
    {
        $json = json_encode($this->offerLabelRelation);

        $expectedJson = '{"uuid":"' . $this->uuid->toNative()
            . '","relationType":"' . $this->relationType->toNative()
            . '","relationId":"' . $this->offerId->toNative() . '"}';

        $this->assertEquals($expectedJson, $json);
    }
}
