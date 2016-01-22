<?php

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\CollaborationData;
use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

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
        return new static(
            new String($data['eventId']),
            new Language($data['language']),
            CollaborationData::deserialize($data['collaborationData'])
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        $serialized = array(
            'eventId' => (string) $this->eventId,
            'language' => $this->language->getCode(),
            'collaborationData' => $this->collaborationData->serialize(),
        );

        return $serialized;
    }
}
