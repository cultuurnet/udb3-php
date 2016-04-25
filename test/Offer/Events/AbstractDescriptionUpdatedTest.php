<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Item\Events\DescriptionUpdated;
use ValueObjects\String\String;

class AbstractDescriptionUpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractDescriptionUpdated
     */
    protected $descriptionUpdated;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var String
     */
    protected $description;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->description = new String('Description');
        $this->descriptionUpdated = new DescriptionUpdated($this->itemId, $this->description);
    }

    /**
     * @test
     */
    public function it_can_be_instantiated_With_properties()
    {
        $expectedItemId = 'Foo';
        $expectedDescription = new String('Description');
        $expectedDescriptionUpdated = new DescriptionUpdated(
            $expectedItemId,
            $expectedDescription
        );

        $this->assertEquals($expectedDescriptionUpdated, $this->descriptionUpdated);
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $expectedItemId = 'Foo';
        $expectedDescription = new String('Description');

        $itemId = $this->descriptionUpdated->getItemId();
        $description = $this->descriptionUpdated->getDescription();

        $this->assertEquals($expectedItemId, $itemId);
        $this->assertEquals($expectedDescription, $description);
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param $expectedSerializedValue
     * @param DescriptionUpdated $descriptionUpdated
     */
    public function it_can_be_serialized_to_an_array(
        $expectedSerializedValue,
        DescriptionUpdated $descriptionUpdated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $descriptionUpdated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param $serializedValue
     * @param DescriptionUpdated $expectedDescriptionUpdated
     */
    public function it_can_deserialize_an_array(
        $serializedValue,
        DescriptionUpdated $expectedDescriptionUpdated
    ) {
        $this->assertEquals(
            $expectedDescriptionUpdated,
            DescriptionUpdated::deserialize($serializedValue)
        );
    }

    /**
     * @return array
     */
    public function serializationDataProvider()
    {
        return [
            'abstractDescriptionUpdated' => [
                [
                    'item_id' => 'madId',
                    'description' => 'Description',
                ],
                new DescriptionUpdated(
                    'madId',
                    new String('Description')
                ),
            ],
        ];
    }
}
