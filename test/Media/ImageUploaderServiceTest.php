<?php

namespace CultuurNet\UDB3\Media;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use League\Flysystem\File;
use League\Flysystem\FilesystemInterface;
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\content\LargeFileContentTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ValueObjects\Number\Natural;
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

    /**
     * @test
     */
    public function it_should_throw_an_exception_if_the_uploaded_file_is_not_an_image()
    {
        $description = new String('file description');
        $copyrightHolder = new String('Dude Man');
        $file = $this->getMockFile();

        $file
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn('video/avi');

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The uploaded file is not an image.'
        );

        $this->uploader->upload($file, $description, $copyrightHolder);
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

    /**
     * @test
     */
    public function it_should_throw_an_exception_when_the_file_size_limit_is_exceeded()
    {
        $uploader = new ImageUploaderService(
            $this->uuidGenerator,
            $this->commandBus,
            $this->filesystem,
            $this->directory,
            new Natural(1000000)
        );

        $file = $this->getMockImage(1111111);

        $description = new String('file description');
        $copyrightHolder = new String('Dude Man');

        $this->setExpectedException(
            FileSizeExceededException::class,
            'The file size of the uploaded image is too big.'
        );

        $uploader->upload($file, $description, $copyrightHolder);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_when_the_file_size_is_limited_but_cannot_be_determined()
    {
        $uploader = new ImageUploaderService(
            $this->uuidGenerator,
            $this->commandBus,
            $this->filesystem,
            $this->directory,
            new Natural(1000000)
        );

        $file = $this->getMockImage(false);

        $description = new String('file description');
        $copyrightHolder = new String('Dude Man');

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'There is a maximum size and we could not determine the size of the uploaded image.'
        );

        $uploader->upload($file, $description, $copyrightHolder);
    }

    /**
     * @test
     */
    public function it_should_upload_a_file_that_does_not_exceed_the_maximum_file_size()
    {
        $file = $this->getMockImage(1000000);

        $uploader = new ImageUploaderService(
            $this->uuidGenerator,
            $this->commandBus,
            $this->filesystem,
            $this->directory,
            new Natural(1000000)
        );

        $description = new String('file description');
        $copyrightHolder = new String('Dude Man');
        $generatedUuid = 'de305d54-75b4-431b-adb2-eb6b9e546014';

        $this->uuidGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn($generatedUuid);

        $expectedDestination = $this->directory.'/'.$generatedUuid.'.jpg';

        $this->filesystem
            ->expects($this->once())
            ->method('writeStream')
            ->with($expectedDestination, $this->anything());

        $uploader->upload($file, $description, $copyrightHolder);
    }

    private function getMockFile()
    {
        return $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();
    }

    /**
     * @param int $imageSize
     *  Image size in bytes.
     *
     * @return UploadedFile|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockImage($imageSize)
    {
        // Use a virtual file that and store its
        $fileDirectory = vfsStream::setup('files');
        $file = vfsStream::newFile('my-image.jpg')
            ->withContent(new LargeFileContent($imageSize))
            ->at($fileDirectory);
        $filePath = $file->url();

        $image = $this->getMockFile();

        $image
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $image
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn('image/jpg');

        $image
            ->expects($this->any())
            ->method('guessExtension')
            ->willReturn('jpg');

        $image->expects($this->any())
            ->method('getRealPath')
            ->willReturn($filePath);

        return $image;
    }
}
