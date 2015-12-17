<?php

namespace CultuurNet\UDB3\Media;

use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class MediaObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_keep_track_of_media_object_meta_data()
    {
        $mediaObject = MediaObject::create(
            UUID::fromNative('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            String::fromNative('sexy ladies without clothes'),
            String::fromNative('Bart Ramakers')
        );

        $this->assertEquals(
            new MIMEType('image/png'),
            $mediaObject->getMimeType()
        );

        $this->assertEquals(
            UUID::fromNative('de305d54-75b4-431b-adb2-eb6b9e546014'),
            $mediaObject->getFileId()
        );

        $this->assertEquals(
            String::fromNative('sexy ladies without clothes'),
            $mediaObject->getDescription()
        );

        $this->assertEquals(
            String::fromNative('Bart Ramakers'),
            $mediaObject->getCopyrightHolder()
        );
    }
}
