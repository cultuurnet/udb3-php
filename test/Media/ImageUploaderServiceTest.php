<?php

namespace CultuurNet\UDB3\Media;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ValueObjects\String\String;

class ImageUploaderServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageUploaderInterface
     */
    protected $uploader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var string
     */
    protected $directory = '/uploads';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CommandBusInterface
     */
    protected $commandBus;

    public function setUp()
    {
        $this->uuidGenerator = $this->getMock(UuidGeneratorInterface::class);
        $this->filesystem = $this->getMock(FilesystemInterface::class);
        $this->commandBus = $this->getMock(CommandBusInterface::class);

        $this->uploader = new ImageUploaderService(
            $this->uuidGenerator,
            $this->commandBus,
            $this->filesystem,
            $this->directory
        );
    }

    public function it_should_check_the_integrity_and_extension_when_uploading_files()
    {

    }

    /**
     * @test
     */
    public function it_should_move_an_uploaded_file_to_the_upload_directory()
    {
        $file = new UploadedFile(
            __DIR__.'/files/my-image.png',
            'my-image.png',
            'image/png',
            null,
            null,
            true
        );

        $description = new String('file description');
        $copyrightHolder = new String('Dude Man');
        $generatedUuid = 'de305d54-75b4-431b-adb2-eb6b9e546014';

        $this->uuidGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn($generatedUuid);

        $expectedDestination = $this->directory.'/'.$generatedUuid.'.png';

        $this->filesystem
            ->expects($this->once())
            ->method('writeStream')
            ->with($expectedDestination, $this->anything());

        $this->uploader->upload($file, $description, $copyrightHolder);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_when_the_upload_was_not_successful()
    {
        $file = new UploadedFile(
            __DIR__.'/files/my-image.png',
            'my-image.png',
            'image/png'
        );
        $description = new String('file description');
        $copyrightHolder = new String('Dude Man');

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The file did not upload correctly.'
        );

        $this->uploader->upload($file, $description, $copyrightHolder);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_when_the_file_type_can_not_be_guessed()
    {
        $file = $this->getMockFile();

        $file
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn(null);

        $description = new String('file description');
        $copyrightHolder = new String('Dude Man');

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The type of the uploaded file can not be guessed.'
        );

        $this->uploader->upload($file, $description, $copyrightHolder);
    }

    private function getMockFile()
    {
        return $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();
    }
}
