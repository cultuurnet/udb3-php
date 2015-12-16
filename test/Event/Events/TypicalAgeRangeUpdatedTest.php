<?php

namespace test\Event\Events;

use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;

class TypicalAgeRangeUpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param TypicalAgeRangeUpdated $typicalAgeRangeUpdated
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        TypicalAgeRangeUpdated $typicalAgeRangeUpdated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $typicalAgeRangeUpdated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $serializedValue
     * @param TypicalAgeRangeUpdated $expectedTypicalAgeRangeUpdated
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        TypicalAgeRangeUpdated $expectedTypicalAgeRangeUpdated
    ) {
        $this->assertEquals(
            $expectedTypicalAgeRangeUpdated,
            TypicalAgeRangeUpdated::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'typical age range' => [
                [
                    'event_id' => 'foo',
                    'typicalAgeRange' => '3-12'
                ],
                new TypicalAgeRangeUpdated(
                    'foo',
                    '3-12'
                ),
            ],
        ];
    }
}
