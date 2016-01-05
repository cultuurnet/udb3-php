<?php

namespace CultuurNet\UDB3\Media;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Media\Events\MediaObjectCreated;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class MediaObjectTest extends AggregateRootScenarioTestCase
{
    /**
     * @inheritdoc
     */
    protected function getAggregateRootClass()
    {
        return MediaObject::class;
    }

    /**
     * @test
     */
    public function it_can_be_created()
    {
        $fileId = UUID::fromNative('de305d54-75b4-431b-adb2-eb6b9e546014');
        $fileType = new MIMEType('image/png');
        $description = String::fromNative('sexy ladies without clothes');
        $copyrightHolder = String::fromNative('Bart Ramakers');

        $this->scenario
            ->withAggregateId($fileId->toNative())
            ->when(
                function () use ($fileId, $fileType, $description, $copyrightHolder) {
                    return MediaObject::create(
                        $fileId,
                        $fileType,
                        $description,
                        $copyrightHolder
                    );
                }
            )
            ->then(
                [
                    new MediaObjectCreated(
                        $fileId,
                        $fileType,
                        $description,
                        $copyrightHolder
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_keep_track_of_media_object_meta_data()
    {
        $mediaObject = MediaObject::create(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new String('sexy ladies without clothes'),
            new String('Bart Ramakers'),
            new String('png')
        );

        $this->assertEquals(
            new MIMEType('image/png'),
            $mediaObject->getMimeType()
        );

        $this->assertEquals(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            $mediaObject->getFileId()
        );

        $this->assertEquals(
            new String('sexy ladies without clothes'),
            $mediaObject->getDescription()
        );

        $this->assertEquals(
            new String('Bart Ramakers'),
            $mediaObject->getCopyrightHolder()
        );
    }
}
