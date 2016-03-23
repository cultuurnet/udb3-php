<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\ContactPoint;

class ContactPointUpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param ContactPointUpdated $contactPointUpdated
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        ContactPointUpdated $contactPointUpdated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $contactPointUpdated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $serializedValue
     * @param ContactPointUpdated $expectedContactPointUpdated
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        ContactPointUpdated $expectedContactPointUpdated
    ) {
        $this->assertEquals(
            $expectedContactPointUpdated,
            ContactPointUpdated::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'contactPointUpdated' => [
                [
                    'item_id' => 'foo',
                    'contactPoint' => [
                        'phone' => [
                            '0123456789',
                            ],
                        'email' => [
                            'foo@bar.com',
                            ],
                        'url' => [
                            'http://foo.bar',
                            ],
                        'type' => 'type',
                    ]
                ],
                new ContactPointUpdated(
                    'foo',
                    new ContactPoint(
                        array('0123456789'),
                        array('foo@bar.com'),
                        array('http://foo.bar'),
                        'type'
                    )
                ),
            ],
        ];
    }
}
