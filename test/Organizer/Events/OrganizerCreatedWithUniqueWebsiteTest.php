<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class OrganizerCreatedWithUniqueWebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_if_an_address_of_an_incorrect_type_is_provided()
    {
        $id = '123';
        $website = Url::fromNative('http://www.stuk.be');
        $title = new Title('Test');

        $addresses = [
            new \stdClass(),
        ];

        $phones = ['12345678'];
        $emails = ['foo@bar.com'];
        $urls = ['http://bar.com'];

        $contactPoint = new ContactPoint($phones, $emails, $urls);

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Argument should be of type Address, stdClass given.'
        );

        new OrganizerCreatedWithUniqueWebsite($id, $website, $title, $addresses, $contactPoint);
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param OrganizerCreatedWithUniqueWebsite $organizerCreated
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        OrganizerCreatedWithUniqueWebsite $organizerCreated
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
     * @param OrganizerCreatedWithUniqueWebsite $expectedOrganizerCreated
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        OrganizerCreatedWithUniqueWebsite $expectedOrganizerCreated
    ) {
        $this->assertEquals(
            $expectedOrganizerCreated,
            OrganizerCreatedWithUniqueWebsite::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'organizerCreatedWithUniqueWebsite' => [
                [
                    'organizer_id' => 'organizer_id',
                    'website' => 'http://www.stuk.be',
                    'title' => 'title',
                    'addresses' => [
                        0 => [
                            'streetAddress' => 'streetAddress',
                            'postalCode' => '3000',
                            'locality' => 'Leuven',
                            'country' => 'Belgium',
                        ],
                    ],
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
                        'type' => '',
                    ],
                ],
                new OrganizerCreatedWithUniqueWebsite(
                    'organizer_id',
                    Url::fromNative('http://www.stuk.be'),
                    new Title('title'),
                    array(new Address('streetAddress', '3000', 'Leuven', 'Belgium')),
                    new ContactPoint(array('0123456789'), array('foo@bar.com'), array('http://foo.bar'))
                ),
            ],
        ];
    }
}
