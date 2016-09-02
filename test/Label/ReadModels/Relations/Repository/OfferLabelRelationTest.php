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
    private $offerType;

    /**
     * @var StringLiteral
     */
    private $offerId;

    /**
     * @var OfferLabelRelation
     */
    private $offerLabelRelation;

    protected function setUp()
    {
        $this->uuid = new UUID();
        $this->offerType = OfferType::PLACE();
        $this->offerId = new StringLiteral('relationId');

        $this->offerLabelRelation = new OfferLabelRelation(
            $this->uuid,
            $this->offerType,
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
            $this->offerType,
            $this->offerLabelRelation->getOfferType()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_relation_id()
    {
        $this->assertEquals($this->offerId, $this->offerLabelRelation->getOfferId());
    }

    /**
     * @test
     */
    public function it_can_encode_to_json()
    {
        $json = json_encode($this->offerLabelRelation);

        $expectedJson = '{"uuid":"' . $this->uuid->toNative()
            . '","offerType":"' . $this->offerType->toNative()
            . '","offerId":"' . $this->offerId->toNative() . '"}';

        $this->assertEquals($expectedJson, $json);
    }
}
