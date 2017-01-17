<?php

namespace CultuurNet\UDB3\Media;

use CultuurNet\UDB3\Media\Events\MediaObjectCreated;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

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
                    'media_object_id' => 'de305d54-75b4-431b-adb2-eb6b9e546014',
                    'mime_type' => 'image/png',
                    'description' => 'sexy ladies without clothes',
                    'copyright_holder' => 'Bart Ramakers',
                    'source_location' => 'http://foo.be/de305d54-75b4-431b-adb2-eb6b9e546014.png'
                ],
                new MediaObjectCreated(
                    new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
                    new MIMEType('image/png'),
                    new StringLiteral('sexy ladies without clothes'),
                    new StringLiteral('Bart Ramakers'),
                    Url::fromNative('http://foo.be/de305d54-75b4-431b-adb2-eb6b9e546014.png')
                )
            ]
        ];
    }
}
