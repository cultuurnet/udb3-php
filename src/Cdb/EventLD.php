<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Cdb;

/**
 * Wrapper around a CultureFeed_Cdb_Item_Event instance, adding special
 * treatment upon JSON serialization.
 */
class EventLD implements \JsonSerializable
{
    /**
     * @var \CultureFeed_Cdb_Item_Event
     */
    protected $event;

    /**
     * @var string
     */
    protected $iri;

    /**
     * @param string $iri
     * @param \CultureFeed_Cdb_Item_Event $event
     */
    public function __construct($iri, \CultureFeed_Cdb_Item_Event $event)
    {
        $this->event = $event;
        $this->iri = $iri;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    function jsonSerialize()
    {
        // @todo Handle language dynamically, currently hardcoded to nl.
        /** @var \CultureFeed_Cdb_Data_EventDetail $detail */
        $language_fallbacks = array('nl', 'en', 'fr', 'de');
        foreach ($language_fallbacks as $language) {
            $detail = $this->event->getDetails()->getDetailByLanguage($language);
            if ($detail) {
                break;
            }
        }
        $pictures = $detail->getMedia()->byMediaType(
            \CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO
        );
        $pictures->rewind();
        $picture = count($pictures) > 0 ? $pictures->current() : null;

        return array(
            // @todo provide Event-LD context here relative to the base URI
            '@context' => '/api/1.0/event.jsonld',
            '@id' => $this->iri,
            'name' => $detail->getTitle(),
            'shortDescription' => $detail->getShortDescription(),
            'calendarSummary' => $detail->getCalendarSummary(),
            'image' => $picture ? $picture->getHLink() : null,
            'location' => $this->event->getLocation()->getLabel(),
        );
    }
} 
