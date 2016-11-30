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
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;
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
        UUID $id,
        MIMEType $fileType,
        StringLiteral $description,
        StringLiteral $copyrightHolder,
        Url $sourceLocation
    ) {
        $mediaObject = MediaObject::create(
            $id,
            $fileType,
            $description,
            $copyrightHolder,
            $sourceLocation
        );

        $this->repository->save($mediaObject);

        return $mediaObject;
    }

    /**
     * {@inheritdoc}
     */
    public function handleUploadImage(UploadImage $uploadImage)
    {
        $pathParts = explode('/', $uploadImage->getFilePath());
        $fileName = array_pop($pathParts);
        $fileNameParts = explode('.', $fileName);
        $extension = StringLiteral::fromNative(array_pop($fileNameParts));
        $destinationPath = $this->pathGenerator->path(
            $uploadImage->getFileId(),
            $extension
        );

        $destinationIri = $this->iriGenerator->iri($destinationPath);

        $this->filesystem->rename(
            $uploadImage->getFilePath(),
            $this->mediaDirectory . '/' . $destinationPath
        );

        $this->create(
            $uploadImage->getFileId(),
            $uploadImage->getMimeType(),
            $uploadImage->getDescription(),
            $uploadImage->getCopyrightHolder(),
            Url::fromNative($destinationIri)
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
                sprintf("Media object with id '%s' not found", $fileId),
                0,
                $e
            );
        }

        return $mediaObject;
    }

    /**
     * @param UUID $imageId
     * @return Image
     * @throws MediaObjectNotFoundException
     */
    public function getImage(UUID $imageId)
    {
        $mediaObject = $this->get($imageId);

        $image = new Image(
            $mediaObject->getMediaObjectId(),
            $mediaObject->getMimeType(),
            $mediaObject->getDescription(),
            $mediaObject->getCopyrightHolder(),
            $mediaObject->getSourceLocation()
        );

        return $image;
    }
}
