<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class OfferLabelRelationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LabelName
     */
    private $labelName;

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
        $this->labelName = new LabelName('2dotstwice');
        $this->relationType = RelationType::PLACE();
        $this->offerId = new StringLiteral('relationId');

        $this->offerLabelRelation = new LabelRelation(
            $this->labelName,
            $this->relationType,
            $this->offerId
        );
    }

    /**
     * @test
     */
    public function it_stores_a_uuid()
    {
        $this->assertEquals($this->labelName, $this->offerLabelRelation->getLabelName());
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

        $expectedJson = '{"labelName":"' . $this->labelName->toNative()
            . '","relationType":"' . $this->relationType->toNative()
            . '","relationId":"' . $this->offerId->toNative() . '"}';

        $this->assertEquals($expectedJson, $json);
    }
}
