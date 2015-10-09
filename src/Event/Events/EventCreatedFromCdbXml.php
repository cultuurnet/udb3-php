<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 07/10/15
 * Time: 11:25
 */

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3SilexEntryAPI\EventXmlString;
use ValueObjects\String\String;

class EventCreatedFromCdbXml implements SerializableInterface
{
    /**
     * @var EventXMLString
     */
    protected $eventXmlString;

    /**
     * @var String
     */
    protected $eventId;

    /**
     * @var String
     */
    protected $cdbXmlNamespaceUri;

    /**
     * @param String $eventId
     * @param EventXmlString $eventXmlString
     * @param String $cdbXmlNamespaceUri
     */
    public function __construct(String $eventId, EventXmlString $eventXmlString, String $cdbXmlNamespaceUri)
    {
        $this->eventXmlString = $eventXmlString;
        $this->eventId = $eventId;
        $this->cdbXmlNamespaceUri = $cdbXmlNamespaceUri;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            new String($data['event_id']),
            new EventXmlString($data['cdbxml']),
            new String($data['cdbXmlNamespaceUri'])
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'event_id' => $this->eventId->toNative(),
            'cdbxml' => $this->eventXmlString->toNative(),
            'cdbXmlNamespaceUri' => $this->cdbXmlNamespaceUri->toNative());
    }

    /**
     * @return EventXmlString
     */
    public function getEventXmlString()
    {
        return $this->eventXmlString;
    }

    /**
     * @return String
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return String
     */
    public function getCdbXmlNamespaceUri()
    {
        return $this->cdbXmlNamespaceUri;
    }
}
