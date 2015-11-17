<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 16/11/15
 * Time: 14:03
 */

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class TranslationDeleted implements SerializableInterface
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
     * TranslationApplied constructor.
     * @param String $eventId
     * @param Language $language
     */
    public function __construct(
        String $eventId,
        Language $language
    ) {
        $this->eventId = $eventId;
        $this->language = $language;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            new String($data['event_id']),
            new Language($data['language'])
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'event_id' => $this->eventId->toNative(),
            'language' => $this->language->getCode()
        );
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
}
