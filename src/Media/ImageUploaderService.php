<?php

namespace CultuurNet\UDB3\Media;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Media\Commands\UploadImage;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class ImageUploaderService implements ImageUploaderInterface
{
    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var CommandBusInterface
     */
    protected $commandBus;

    /**
     * @var string
     */
    protected $uploadDirectory;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @param UuidGeneratorInterface $uuidGenerator
     * @param CommandBusInterface $commandBus
     * @param FilesystemInterface $filesystem
     * @param $uploadDirectory
     */
    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        CommandBusInterface $commandBus,
        FilesystemInterface $filesystem,
        $uploadDirectory
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->commandBus = $commandBus;
        $this->filesystem = $filesystem;
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * @inheritdoc
     */
    public function upload(UploadedFile $file, String $description, String $copyrightHolder)
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('The file did not upload correctly.');
        }

        $fileType = $file->getMimeType();

        if (!$fileType) {
            throw new \InvalidArgumentException('The type of the uploaded file can not be guessed.');
        }

        $mimeType = MIMEType::fromNative($fileType);

        $fileId = new UUID($this->uuidGenerator->generate());
        $fileName = $fileId . '.' . $file->guessExtension();
        $destination = $this->getUploadDirectory() . '/' . $fileName;
        $stream = fopen($file->getRealPath(), 'r+');
        $this->filesystem->writeStream($destination, $stream);
        fclose($stream);

        return $this->commandBus->dispatch(
            new UploadImage($fileId, $mimeType, $description, $copyrightHolder)
        );
    }

    /**
     * @inheritdoc
     */
    public function getUploadDirectory()
    {
        return $this->uploadDirectory;
    }
}
