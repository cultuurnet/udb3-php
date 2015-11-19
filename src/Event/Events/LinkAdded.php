<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 19/11/15
 * Time: 08:59
 */

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\LinkType;
use ValueObjects\String\String;

class LinkAdded implements SerializableInterface
{
    /**
     * @var String|String
     */
    protected $eventId;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var String|String
     */
    protected $link;

    /**
     * @var LinkType
     */
    protected $linkType;

    /**
     * @var String|String
     */
    protected $title;

    /**
     * @var String|String
     */
    protected $copyright;

    /**
     * @var String|String
     */
    protected $subbrand;

    /**
     * @var String|String
     */
    protected $description;

    public function __construct(
        String $eventId,
        Language $language,
        String $link,
        LinkType $linkType,
        String $title = null,
        String $copyright = null,
        String $subbrand = null,
        String $description = null
    ) {
        $this->eventId = $eventId;
        $this->language = $language;
        $this->link = $link;
        $this->linkType = $linkType;
        $this->title = $title;
        $this->copyright = $copyright;
        $this->subbrand = $subbrand;
        $this->description = $description;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        $title = null;
        $copyright = null;
        $subbrand = null;
        $description = null;

        if (isset($data['title'])) {
            $title = new String($data['title']);
        }

        if (isset($data['copyright'])) {
            $copyright = new String($data['copyright']);
        }

        if (isset($data['sub_brand'])) {
            $subbrand = new String($data['sub_brand']);
        }

        if (isset($data['description'])) {
            $description = new String($data['description']);
        }

        return new static(
            new String($data['event_id']),
            new Language($data['language']),
            new String($data['link']),
            new LinkType($data['link_type']),
            $title,
            $copyright,
            $subbrand,
            $description
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        $serialized = array(
            'event_id' => $this->eventId->toNative(),
            'language' => $this->language->getCode(),
            'link' => $this->link->toNative(),
            'link_type' => $this->linkType->toNative()
        );

        if ($this->title) {
            $serialized['title'] = $this->title->toNative();
        }

        if ($this->copyright) {
            $serialized['copyright'] = $this->copyright->toNative();
        }

        if ($this->subbrand) {
            $serialized['sub_brand'] = $this->subbrand->toNative();
        }

        if ($this->description) {
            $serialized['description'] = $this->description->toNative();
        }

        return $serialized;
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
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return LinkType
     */
    public function getLinkType()
    {
        return $this->linkType;
    }

    /**
     * @return String|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return String|null
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @return String|null
     */
    public function getSubbrand()
    {
        return $this->subbrand;
    }

    /**
     * @return String|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
