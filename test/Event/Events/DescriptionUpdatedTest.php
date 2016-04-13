<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Events;

class DescriptionUpdatedTest extends \PHPUnit_Framework_TestCase
{
    public function serializationDataProvider()
    {
        return [
            [
                [
                    'item_id' => 'event-123',
                    'description' => 'description-456',
                ],
                new DescriptionUpdated(
                    'event-123',
                    'description-456'
                ),
            ]
        ];
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_serialized_to_an_array(
        array $expectedSerializedValue,
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
     */
    public function it_can_be_deserialized_from_an_array(
        array $serializedValue,
        DescriptionUpdated $expectedDescriptionUpdated
    ) {
        $this->assertEquals(
            $expectedDescriptionUpdated,
            DescriptionUpdated::deserialize($serializedValue)
        );
    }
}
