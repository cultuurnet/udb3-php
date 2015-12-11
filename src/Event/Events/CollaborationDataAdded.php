<?php

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\CollaborationData\CollaborationData;
use CultuurNet\UDB3\CollaborationData\CollaborationDataPropertiesTrait;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\CollaborationData\Description;
use CultuurNet\UDB3\Link\LinkType;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

final class CollaborationDataAdded implements SerializableInterface
{
    /**
     * @var String
     */
    protected $eventId;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var CollaborationData
     */
    protected $collaborationData;

    /**
     * @param String $eventId
     * @param Language $language
     * @param CollaborationData $collaborationData
     */
    public function __construct(
        String $eventId,
        Language $language,
        CollaborationData $collaborationData
    ) {
        $this->eventId = $eventId;
        $this->language = $language;
        $this->collaborationData = $collaborationData;
    }

    /**
     * @return String
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return CollaborationData
     */
    public function getCollaborationData()
    {
        return $this->collaborationData;
    }

    /**
     * @param array $data
     * @return static The object instance
     */
    public static function deserialize(array $data)
    {
        $description = json_decode($data['description']);

        if (is_null($description)) {
            throw new \InvalidArgumentException('Description is not valid JSON.');
        }

        $collaborationData = new CollaborationData(
            new String($data['sub_brand']),
            new String($description['text'])
        );

        if (isset($data['title'])) {
            $collaborationData = $collaborationData
                ->withTitle(
                    new String($data['title'])
                );
        }

        if (isset($data['copyright'])) {
            $collaborationData = $collaborationData
                ->withCopyright(
                    new String($data['copyright'])
                );
        }

        if (isset($data['link'])) {
            $collaborationData = $collaborationData
                ->withLink(
                    Url::fromNative($data['link'])
                );
        }

        if (isset($description['keyword'])) {
            $collaborationData = $collaborationData
                ->withKeyword(
                    new String($data['keyword'])
                );
        }

        if (isset($description['image'])) {
            $collaborationData = $collaborationData
                ->withImage(
                    new String($data['image'])
                );
        }

        if (isset($description['article'])) {
            $collaborationData = $collaborationData
                ->withArticle(
                    new String($data['article'])
                );
        }

        $added = new CollaborationDataAdded(
            new String($data['event_id']),
            new Language($data['language']),
            $collaborationData
        );

        return $added;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        $description = [
            'text' => (string) $this->collaborationData->getText(),
            'keyword' => (string) $this->collaborationData->getKeyword(),
            'image' => (string) $this->collaborationData->getImage(),
            'article' => (string) $this->collaborationData->getArticle(),
        ];

        $description = json_encode(
            array_filter($description, 'strlen')
        );

        $serialized = array(
            'event_id' => (string) $this->eventId,
            'language' => $this->language->getCode(),
            'link' => (string) $this->collaborationData->getLink(),
            'link_type' => 'collaboration',
            'sub_brand' => (string) $this->collaborationData->getSubBrand(),
            'description' => $description,
            'title' => (string) $this->collaborationData->getTitle(),
            'copyright' => (string) $this->collaborationData->getCopyright(),
        );

        $serialized = array_filter($serialized, 'strlen');

        return $serialized;
    }
}
