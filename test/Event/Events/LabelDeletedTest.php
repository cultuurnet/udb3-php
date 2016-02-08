<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Label;

class LabelDeletedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param Unlabelled $unlabelled
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        LabelDeleted $unlabelled
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $unlabelled->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $serializedValue
     * @param Unlabelled $expectedUnlabelled
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        LabelDeleted $expectedUnlabelled
    ) {
        $this->assertEquals(
            $expectedUnlabelled,
            LabelDeleted::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'unlabelled' => [
                [
                    'item_id' => 'foo',
                    'label' => 'Label1'
                ],
                new LabelDeleted(
                    'foo',
                    new Label('Label1')
                ),
            ],
        ];
    }
}
