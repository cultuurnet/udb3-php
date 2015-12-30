<?php

namespace CultuurNet\UDB3\Organizer\Events;

class OrganizerEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param MockOrganizerEvent $organizerEvent
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        MockOrganizerEvent $organizerEvent
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $organizerEvent->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $serializedValue
     * @param MockOrganizerEvent $expectedOrganizerEvent
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        MockOrganizerEvent $expectedUnlabelled
    ) {
        $this->assertEquals(
            $expectedUnlabelled,
            MockOrganizerEvent::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'organizerEvent' => [
                [
                    'organizer_id' => 'organizer_id',
                ],
                new MockOrganizerEvent(
                    'organizer_id'
                ),
            ],
        ];
    }
}
