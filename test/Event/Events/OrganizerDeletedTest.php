<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Events;

class OrganizerDeletedTest extends \PHPUnit_Framework_TestCase
{
    public function serializationDataProvider()
    {
        return [
            [
                [
                    'item_id' => 'event-123',
                    'organizerId' => 'organizer-456',
                ],
                new OrganizerDeleted(
                    'event-123',
                    'organizer-456'
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
        OrganizerDeleted $organizerDeleted
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $organizerDeleted->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_deserialized_from_an_array(
        array $serializedValue,
        OrganizerDeleted $expectedOrganizerDeleted
    ) {
        $this->assertEquals(
            $expectedOrganizerDeleted,
            OrganizerDeleted::deserialize($serializedValue)
        );
    }
}
