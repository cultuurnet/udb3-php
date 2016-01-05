<?php

namespace CultuurNet\UDB3\Media;

use CultuurNet\UDB3\Media\Events\MediaObjectCreated;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class MediaObjectCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_should_include_all_properties_when_serializing(
        $expectedSerializedValue,
        MediaObjectCreated $mediaObjectCreated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $mediaObjectCreated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_should_set_all_the_properties_when_deserializing(
        $serializedValue,
        MediaObjectCreated $expectedMediaObjectCreated
    ) {
        $this->assertEquals(
            $expectedMediaObjectCreated,
            MediaObjectCreated::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'creationEvent' => [
                [
                    'file_id' => 'de305d54-75b4-431b-adb2-eb6b9e546014',
                    'mime_type' => 'image/png',
                    'description' => 'sexy ladies without clothes',
                    'copyright_holder' => 'Bart Ramakers',
                    'extension' => 'png'
                ],
                new MediaObjectCreated(
                    new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
                    new MIMEType('image/png'),
                    new String('sexy ladies without clothes'),
                    new String('Bart Ramakers'),
                    new String('png')
                )
            ]
        ];
    }
}
