<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 27/10/15
 * Time: 12:28
 */

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\KeywordsString;
use ValueObjects\String\String;

class LabelsApplied implements SerializableInterface
{
    /**
     * @var KeywordsString
     */
    protected $keywordsString;

    /**
     * @var String
     */
    protected $eventId;

    /**
     * @param String $eventId
     * @param KeywordsString $keywordsString
     */
    public function __construct(String $eventId, KeywordsString $keywordsString)
    {
        $this->keywordsString = $keywordsString;
        $this->eventId = $eventId;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            new String($data['event_id']),
            new KeywordsString($data['keywords_string'])
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'event_id' => $this->eventId->toNative(),
            'keywords_string' => $this->keywordsString->toNative()
        );
    }

    /**
     * @return KeywordsString
     */
    public function getKeywordsString()
    {
        return $this->keywordsString;
    }

    /**
     * @return String
     */
    public function getEventId()
    {
        return $this->eventId;
    }
}
