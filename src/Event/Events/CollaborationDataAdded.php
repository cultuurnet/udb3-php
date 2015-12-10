<?php

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\CollaborationData\Description;
use CultuurNet\UDB3\Link\LinkType;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

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
     * @var Url|null
     */
    protected $url;

    /**
     * @var String|null
     */
    protected $title;

    /**
     * @var String|null
     */
    protected $copyright;

    /**
     * @var String
     */
    protected $subbrand;

    /**
     * @var Description
     */
    protected $description;

    /**
     * @param String $eventId
     * @param Language $language
     * @param String $subbrand
     * @param Description $description
     */
    public function __construct(
        String $eventId,
        Language $language,
        String $subbrand,
        Description $description
    ) {
        $this->eventId = $eventId;
        $this->language = $language;
        $this->subbrand = $subbrand;
        $this->description = $description;
    }

    /**
     * @param String $title
     * @return static
     */
    public function withTitle(String $title)
    {
        $c = clone $this;
        $c->title = $title;
        return $c;
    }

    /**
     * @param String $copyright
     * @return static
     */
    public function withCopyright(String $copyright)
    {
        $c = clone $this;
        $c->copyright = $copyright;
        return $c;
    }

    /**
     * @param Url $url
     * @return static
     */
    public function withUrl(Url $url)
    {
        $c = clone $this;
        $c->url = $url;
        return $c;
    }

    /**
     * @param array $data
     * @return static The object instance
     */
    public static function deserialize(array $data)
    {
        $added = new CollaborationDataAdded(
            new String($data['event_id']),
            new Language($data['language']),
            new String($data['sub_brand']),
            new Description($data['description'])
        );

        if (isset($data['title'])) {
            $added = $added->withTitle(
                new String($data['title'])
            );
        }

        if (isset($data['copyright'])) {
            $added = $added->withCopyright(
                new String($data['copyright'])
            );
        }

        if (isset($data['link'])) {
            $added = $added->withUrl(
                Url::fromNative($data['link'])
            );
        }

        return $added;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        $serialized = array(
            'event_id' => $this->eventId->toNative(),
            'language' => $this->language->getCode(),
            'link_type' => 'collaboration',
            'sub_brand' => $this->subbrand->toNative(),
            'description' => $this->description->toNative(),
        );

        if ($this->title) {
            $serialized['title'] = (string) $this->title;
        }

        if ($this->copyright) {
            $serialized['copyright'] = (string) $this->copyright;
        }

        if ($this->url) {
            $serialized['link'] = (string) $this->url;
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
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
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
     * @return Description|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
