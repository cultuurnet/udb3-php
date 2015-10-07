<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 07/10/15
 * Time: 11:25
 */

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\XmlString;
use ValueObjects\String\String;

class EventCreatedFromCdbXml implements SerializableInterface
{
    /**
     * @var XMLString
     */
    protected $xmlString;

    /**
     * @var String
     */
    protected $eventId;

    public function __construct(String $eventId, XmlString $xmlString)
    {
        $this->xmlString = $xmlString;
        $this->eventId = $eventId;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            new String($data['event_id']),
            new XmlString($data['cdbxml'])
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array('event_id' => $this->eventId->toNative(), 'cdbxml' => $this->xmlString->toNative());
    }

    /**
     * @return XmlString
     */
    public function getXmlString()
    {
        return $this->xmlString;
    }

    /**
     * @return String
     */
    public function getEventId()
    {
        return $this->eventId;
    }
}
