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
        $this->event = $event;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    function jsonSerialize()
    {
        // @todo Handle language dynamically, currently hardcoded to nl.
        /** @var \CultureFeed_Cdb_Data_EventDetail $detail */
        $detail = $this->event->getDetails()->getDetailByLanguage('nl');
        $pictures = $detail->getMedia()->byMediaType(
            \CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO
        );
        $pictures->rewind();
        $picture = count($pictures) > 0 ? $pictures->current() : null;

        return array(
            // @todo provide Event-LD context here relative to the base URI
            '@context' => '/api/1.0/event.jsonld',
            // @todo make id a dereferenceable URI (http://en.wikipedia.org/wiki/Dereferenceable_Uniform_Resource_Identifier)
            '@id' => $this->event->getCdbId(),
            'name' => $detail->getTitle(),
            'shortDescription' => $detail->getShortDescription(),
            'calendarSummary' => $detail->getCalendarSummary(),
            'image' => $picture ? $picture->getHLink() : null,
            'location' => $this->event->getLocation()->getLabel(),
        );
    }
} 
