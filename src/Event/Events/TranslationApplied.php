<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 04/11/15
 * Time: 17:35
 */

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class TranslationApplied implements SerializableInterface
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
     * @var String
     */
    protected $title;

    /**
     * @var String
     */
    protected $shortDescription;

    /**
     * @var String
     */
    protected $longDescription;

    /**
     * TranslationApplied constructor.
     * @param String $eventId
     * @param Language $language
     * @param String|null $title
     * @param String|null $shortDescription
     * @param String|null $longDescription
     */
    public function __construct(
        String $eventId,
        Language $language,
        String $title = null,
        String $shortDescription = null,
        String $longDescription = null
    ) {
        $this->eventId = $eventId;
        $this->language = $language;
        $this->title = $title;
        $this->shortDescription = $shortDescription;
        $this->longDescription = $longDescription;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            new String($data['event_id']),
            new Language($data['language']),
            new String($data['title']),
            new String($data['short_description']),
            new String($data['long_description'])
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'event_id' => $this->eventId->toNative(),
            'language' => $this->language->getCode(),
            'title' => $this->title->toNative(),
            'short_description' => $this->shortDescription->toNative(),
            'long_description' => $this->longDescription->toNative()
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

    /**
     * @return String
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return String
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @return String
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }
}
