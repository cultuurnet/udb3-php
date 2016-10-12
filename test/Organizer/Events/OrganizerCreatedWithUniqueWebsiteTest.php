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
                ],
                new OrganizerCreatedWithUniqueWebsite(
                    'organizer_id',
                    Url::fromNative('http://www.stuk.be'),
                    new Title('title')
                ),
            ],
        ];
    }
}
