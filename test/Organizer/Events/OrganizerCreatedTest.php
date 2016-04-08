<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated;
use CultuurNet\UDB3\Title;

class OrganizerCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_if_an_address_of_an_incorrect_type_is_provided()
    {
        $id = '123';
        $title = new Title('Test');

        $addresses = [
            new \stdClass(),
        ];

        $phones = ['12345678'];
        $emails = ['foo@bar.com'];
        $urls = ['http://bar.com'];

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Argument should be of type Address, stdClass given.'
        );

        new OrganizerCreated($id, $title, $addresses, $phones, $emails, $urls);
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param OrganizerCreated $organizerCreated
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        OrganizerCreated $organizerCreated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $organizerCreated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $serializedValue
     * @param OrganizerCreated $expectedOrganizerCreated
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        OrganizerCreated $expectedOrganizerCreated
    ) {
        $this->assertEquals(
            $expectedOrganizerCreated,
            OrganizerCreated::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'organizerCreated' => [
                [
                    'organizer_id' => 'organizer_id',
                    'title' => 'title',
                    'addresses' => [
                        0 => [
                            'streetAddress' => 'streetAddress',
                            'postalCode' => '3000',
                            'locality' => 'Leuven',
                            'country' => 'Belgium',
                        ],
                    ],
                    'phones' => [
                        '0123456789',
                    ],
                    'emails' => [
                        'foo@bar.com',
                    ],
                    'urls' => [
                        'http://foo.bar',
                    ],
                ],
                new OrganizerCreated(
                    'organizer_id',
                    new Title('title'),
                    array(new Address('streetAddress', '3000', 'Leuven', 'Belgium')),
                    array('0123456789'),
                    array('foo@bar.com'),
                    array('http://foo.bar')
                ),
            ],
        ];
    }
}
