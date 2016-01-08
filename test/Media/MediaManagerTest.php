<?php

namespace CultuurNet\UDB3\Media;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Media\Commands\UploadImage;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class MediaManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MediaManager
     */
    protected $mediaManager;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var IriGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $iriGenerator;

    /**
     * @var PathGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pathGenerator;

    /**
     * @var string
     */
    protected $mediaDirectory = '/media';

    /**
     * @var FilesystemInterface|\PHPUnit_Framework_MockObject_MockObject;
     */
    protected $filesystem;

    public function setUp()
    {
        $this->repository = $this->getMock(RepositoryInterface::class);
        $this->iriGenerator = $this->getMock(IriGeneratorInterface::class);
        $this->pathGenerator = $this->getMock(PathGeneratorInterface::class);
        $this->filesystem = $this->getMock(FilesystemInterface::class);

        $this->mediaManager = new MediaManager(
            $this->iriGenerator,
            $this->pathGenerator,
            $this->repository,
            $this->filesystem,
            $this->mediaDirectory
        );
    }

    /**
     * @test
     */
    public function it_should_log_the_file_id_after_a_media_object_is_created_for_an_uploaded_image()
    {
        $command = new UploadImage(
            UUID::fromNative('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            String::fromNative('description'),
            String::fromNative('copyright'),
            String::fromNative('/uploads/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );

        $logger = $this->getMock(LoggerInterface::class);
        $this->mediaManager->setLogger($logger);

        $this->iriGenerator
            ->expects($this->once())
            ->method('iri')
            ->willReturn('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png');

        $logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'job_info',
                ['file_id' => 'de305d54-75b4-431b-adb2-eb6b9e546014']
            );

        $this->mediaManager->handleUploadImage($command);
    }

    /**
     * @test
     */
    public function it_should_move_a_file_to_the_media_directory_when_uploading()
    {
        $command = new UploadImage(
            UUID::fromNative('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            String::fromNative('description'),
            String::fromNative('copyright'),
            String::fromNative('/uploads/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );

        $this->pathGenerator
            ->expects($this->once())
            ->method('path')
            ->willReturn('de305d54-75b4-431b-adb2-eb6b9e546014.png');

        $this->iriGenerator
            ->expects($this->once())
            ->method('iri')
            ->willReturn('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png');

        $this->filesystem
            ->expects($this->once())
            ->method('rename')
            ->with(
                '/uploads/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                '/media/de305d54-75b4-431b-adb2-eb6b9e546014.png'
            );

        $this->mediaManager->handleUploadImage($command);
    }
}
