<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\Events\ImageUpdated;
use CultuurNet\UDB3\Media\MediaObject;

class ImageUpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param ImageUpdated $imageUpdated
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        ImageUpdated $imageUpdated
    ) {
        $this->markTestIncomplete(
            'Switch to Image value object then reimplement test.'
        );

        $this->assertEquals(
            $expectedSerializedValue,
            $imageUpdated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider deserializationDataProvider
     * @param array $serializedValue
     * @param ImageUpdated $expectedTypicalAgeRangeUpdated
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        ImageUpdated $expectedImageeUpdated
    ) {

        $this->markTestIncomplete(
            'Switch to Image value object then reimplement test.'
        );

        $this->assertEquals(
            $expectedImageeUpdated,
            ImageUpdated::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'imageUpdated' => [
                [
                    'event_id' => 'foo',
                    'index_to_update' => 'indexToUpdate',
                    'media_object' => [
                        'type' => '',
                        'url' => 'url',
                        'thumbnail_url' => 'urlThumbnail',
                        'description' => 'description',
                        'copyright_holder' => 'copyright',
                        'internal_id' => ''
                    ]
                ],
                new ImageUpdated(
                    'foo',
                    'indexToUpdate',
                    new MediaObject(
                        'url',
                        'urlThumbnail',
                        'description',
                        'copyright'
                    )
                ),
            ],
        ];
    }

    public function deserializationDataProvider()
    {
        return [
            'imageUpdated' => [
                [
                    'place_id' => 'foo',
                    'index_to_update' => 'indexToUpdate',
                    'media_object' => [
                        'type' => '',
                        'url' => 'url',
                        'thumbnail_url' => 'urlThumbnail',
                        'description' => 'description',
                        'copyright_holder' => 'copyright',
                        'internal_id' => ''
                    ]
                ],
                new ImageUpdated(
                    'foo',
                    'indexToUpdate',
                    new MediaObject(
                        'url',
                        'urlThumbnail',
                        'description',
                        'copyright'
                    )
                ),
            ],
        ];
    }
}
