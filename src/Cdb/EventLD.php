<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Cdb;


class EventLD implements \JsonSerializable
{
    /**
     * @var \CultureFeed_Cdb_Item_Event
     */
    protected $event;

    public function __construct(\CultureFeed_Cdb_Item_Event $event)
    {

    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    function jsonSerialize()
    {
        return array(
            '@context' => '/api/1.0/event.jsonld',
        );
    }
} 
