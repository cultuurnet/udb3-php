<?php

namespace CultuurNet\UDB3\ImageAsset;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class ImageUploaderService extends Udb3CommandHandler implements ImageUploaderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * @var string
     */
    protected $imageDirectory;

    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        CommandBusInterface $commandBus,
        $uploadDirectory,
        $imageDirectory
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->commandBus = $commandBus;
        $this->uploadDirectory = $uploadDirectory;
        $this->imageDirectory = $imageDirectory;
        $this->setLogger(new NullLogger());
    }

    /**
     * @inheritdoc
     */
    public function upload(UploadedFile $file, String $description, String $copyrightHolder)
    {
        $fileId = new UUID($this->uuidGenerator->generate());

        $fileType = $file->getMimeType();

        if (!$fileType) {
            throw new \InvalidArgumentException('The type of the uploaded file can not be guessed.');
        }

        $file->move(
            $this->getUploadDirectory(),
            $fileId.'.'.$file->guessExtension()
        );

        return $this->commandBus->dispatch(
            new UploadImage($fileId, $fileType, $description, $copyrightHolder)
        );
    }

    /**
     * @inheritdoc
     */
    public function getUploadDirectory()
    {
        return $this->uploadDirectory;
    }

    /**
     * @return string
     */
    public function getImageDirectory()
    {
        return $this->imageDirectory;
    }

    public function handleUploadImage(UploadImage $uploadImage)
    {
        $fileId = (string) $uploadImage->getFileId();
        $extensionGuesser = ExtensionGuesser::getInstance();
        $fileName = $fileId.'.'.$extensionGuesser->guess($uploadImage->getFileType());

        rename($this->uploadDirectory.'/'.$fileName, $this->imageDirectory.'/'.$fileName);

        $jobInfo = ['file_id' => $fileId];
        $this->logger->info('job_info', $jobInfo);
    }
}
