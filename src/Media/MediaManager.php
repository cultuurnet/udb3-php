<?php

namespace CultuurNet\UDB3\Media;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Media\Commands\UploadImage;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

class MediaManager extends Udb3CommandHandler implements LoggerAwareInterface, MediaManagerInterface
{
    use LoggerAwareTrait;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @var string
     */
    protected $mediaDirectory;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var PathGeneratorInterface
     */
    protected $pathGenerator;

    public function __construct(
        IriGeneratorInterface $iriGenerator,
        PathGeneratorInterface $pathGenerator,
        RepositoryInterface $repository,
        FilesystemInterface $filesystem,
        $mediaDirectory
    ) {
        $this->iriGenerator = $iriGenerator;
        $this->pathGenerator = $pathGenerator;
        $this->mediaDirectory = $mediaDirectory;
        $this->filesystem = $filesystem;
        $this->repository = $repository;

        // Avoid conditional log calls by setting a null logger by default.
        $this->setLogger(new NullLogger());
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        UUID $fileId,
        MIMEType $fileType,
        String $description,
        String $copyrightHolder,
        String $extension
    ) {
        $mediaObject = MediaObject::create(
            $fileId,
            $fileType,
            $description,
            $copyrightHolder,
            $extension
        );

        $this->repository->save($mediaObject);

        return $mediaObject;
    }

    private function generateUrl(MediaObject $mediaObject)
    {
        $extensionGuesser = ExtensionGuesser::getInstance();
        $fileExtension = $extensionGuesser->guess((string) $mediaObject->getMimeType());
        $fileId = $mediaObject->getFileId();
        $filePath = $this->pathGenerator->path(
            $mediaObject->getFileId(),
            new String($fileExtension)
        );

        return Url::fromNative($this->iriGenerator->iri($filePath));
    }

    /**
     * {@inheritdoc}
     */
    public function handleUploadImage(UploadImage $uploadImage)
    {
        $pathParts = explode('/', $uploadImage->getFilePath());
        $fileName = array_pop($pathParts);
        $fileNameParts = explode('.', $fileName);
        $extension = String::fromNative(array_pop($fileNameParts));
        $destination = $this->mediaDirectory . '/' . $this->pathGenerator->path(
            $uploadImage->getFileId(),
            $extension
        );

        $this->filesystem->rename($uploadImage->getFilePath(), $destination);

        $this->create(
            $uploadImage->getFileId(),
            $uploadImage->getMimeType(),
            $uploadImage->getDescription(),
            $uploadImage->getCopyrightHolder(),
            $extension
        );

        $jobInfo = ['file_id' => (string) $uploadImage->getFileId()];
        $this->logger->info('job_info', $jobInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function get(UUID $fileId)
    {
        try {
            $mediaObject = $this->repository->load((string) $fileId);
        } catch (AggregateNotFoundException $e) {
            throw new MediaObjectNotFoundException(
                sprintf("Media object with id '%s' not found", $fileId), 0, $e
            );
        }
        $mediaObject->setUrl($this->generateUrl($mediaObject));

        return $mediaObject;
    }
}
