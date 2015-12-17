<?php

namespace CultuurNet\UDB3\Media;

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
     * @var string
     */
    protected $uploadDirectory;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function __construct(
        IriGeneratorInterface $iriGenerator,
        RepositoryInterface $repository,
        FilesystemInterface $filesystem,
        $uploadDirectory,
        $mediaDirectory
    ) {
        $this->iriGenerator = $iriGenerator;
        $this->mediaDirectory = $mediaDirectory;
        $this->uploadDirectory = $uploadDirectory;
        $this->filesystem = $filesystem;
        $this->repository = $repository;

        // Avoid conditional log calls by setting a null logger by default.
        $this->setLogger(new NullLogger());
    }

    /**
     * {@inheritdoc}
     */
    public function create(UUID $fileId, MIMEType $fileType, String $description, String $copyrightHolder)
    {
        $mediaObject = MediaObject::create($fileId, $fileType, $description, $copyrightHolder);

        $this->repository->save($mediaObject);

        return $mediaObject;
    }

    private function generateUrl(MediaObject $mediaObject)
    {
        $extensionGuesser = ExtensionGuesser::getInstance();
        $fileExtension = $extensionGuesser->guess((string) $mediaObject->getMimeType());
        $fileId = $mediaObject->getFileId();

        return Url::fromNative($this->iriGenerator->iri($fileId.'.'.$fileExtension));
    }

    /**
     * {@inheritdoc}
     */
    public function handleUploadImage(UploadImage $uploadImage)
    {
        $fileId = $uploadImage->getFileId();
        $mimeType = $uploadImage->getMimeType();
        $extensionGuesser = ExtensionGuesser::getInstance();
        $extension = $extensionGuesser->guess((string) $mimeType);
        $fileName = (string) $uploadImage->getFileId().'.'.$extension;

        $this->filesystem->rename(
            $this->uploadDirectory.'/'.$fileName,
            $this->mediaDirectory.'/'.$fileName
        );

        $this->create(
            $fileId,
            $mimeType,
            $uploadImage->getDescription(),
            $uploadImage->getCopyrightHolder()
        );

        $jobInfo = ['file_id' => (string) $fileId];
        $this->logger->info('job_info', $jobInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function get(UUID $fileId)
    {
        $mediaObject = $this->repository->load((string) $fileId);
        $mediaObject->setUrl($this->generateUrl($mediaObject));

        return $mediaObject;
    }
}
