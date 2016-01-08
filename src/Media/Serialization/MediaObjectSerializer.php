<?php

namespace CultuurNet\UDB3\Media\Serialization;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\Serializer\Exception\UnsupportedException;
use Symfony\Component\Serializer\SerializerInterface;
use ValueObjects\Web\Url;

class MediaObjectSerializer implements SerializerInterface
{
    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * MediaObjectSerializer constructor.
     * @param IriGeneratorInterface $iriGenerator
     */
    public function __construct(
        IriGeneratorInterface $iriGenerator
    ) {
        $this->iriGenerator = $iriGenerator;
    }

    public function serialize($mediaObject, $format, array $context = array())
    {
        if (!isset($format) || $format !== 'json-ld') {
            throw new UnsupportedException('Unsupported format, only json-ld is available.');
        };

        $normalizedData = [
            '@id' => (string) $this->generateId($mediaObject),
            '@type' => $this->serializeMimeType($mediaObject->getMimeType()),
            'contentUrl' => (string) $mediaObject->getSourceLocation(),
            'thumbnailUrl' => (string) $mediaObject->getSourceLocation(),
            'description' => (string) $mediaObject->getDescription(),
            'copyrightHolder' => (string) $mediaObject->getCopyrightHolder(),
        ];

        return $normalizedData;
    }

    private function generateId(MediaObject $mediaObject)
    {
        $id = (string) $mediaObject->getMediaObjectId();

        return Url::fromNative($this->iriGenerator->iri($id));
    }

    public function serializeMimeType(MIMEType $mimeType)
    {
        $typeParts = explode('/', (string) $mimeType);
        $type = array_shift($typeParts);

        if ($type !== 'image') {
            throw new UnsupportedException('Unsupported MIME-type, only images are allowed');
        }

        return 'schema:ImageObject';
    }

    public function deserialize($data, $type, $format, array $context = array())
    {
        throw new \Exception('deserialization currently not supported');
    }
}
