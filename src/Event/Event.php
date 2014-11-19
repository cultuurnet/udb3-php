<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Language;

class Event extends EventSourcedAggregateRoot
{
    protected $eventId;
    protected $keywords = array();

    /**
     * Factory method to create a new event.
     *
     * @param string $eventId
     * @return Event
     */
    static public function create($eventId)
    {
        $event = new self();
        $event->apply(new EventCreated($eventId));

        return $event;
    }

    static public function importFromUDB2($eventId, $cdbXml)
    {
        $event = new self();
        $event->apply(new EventImportedFromUDB2($eventId, $cdbXml));

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->eventId;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function tag($keyword)
    {
        if (in_array($keyword, $this->keywords)) {
            return;
        }

        $this->apply(new EventWasTagged($this->eventId, $keyword));
    }

    public function eraseTag($keyword)
    {
        if (!in_array($keyword, $this->keywords)) {
            return;
        }

        $this->apply(new TagErased($this->eventId, $keyword));
    }

    protected function applyEventCreated(EventCreated $eventCreated)
    {
        $this->eventId = $eventCreated->getEventId();
    }

    protected function applyEventWasTagged(EventWasTagged $eventTagged)
    {
        $this->keywords[] = $eventTagged->getKeyword();
    }

    protected function applyTagErased(TagErased $tagErased)
    {
        $this->keywords = array_filter(
            $this->keywords,
            function (Keyword $keyword) use ($tagErased) {
                return $keyword !== $tagErased->getKeyword();
            }
        );
    }

    protected function applyEventImportedFromUDB2(EventImportedFromUDB2 $eventImported)
    {
        $this->eventId = $eventImported->getEventId();
        $cdbXml = $eventImported->getCdbXml();

        $udb2SimpleXml = new \SimpleXMLElement(
            $cdbXml,
            0,
            false,
            \CultureFeed_Cdb_Default::CDB_SCHEME_URL
        );

        $udb2Event = \CultureFeed_Cdb_Item_Event::parseFromCdbXml(
            $udb2SimpleXml
        );

        $this->keywords = array_values($udb2Event->getKeywords());
    }

    /**
     * @param Language $language
     * @param string $title
     */
    public function translateTitle(Language $language, $title)
    {
        $this->apply(new TitleTranslated($this->eventId, $language, $title));
    }

    public function translateDescription(Language $language, $description)
    {
        $this->apply(new DescriptionTranslated($this->eventId, $language, $description));
    }

    protected function applyTitleTranslated(TitleTranslated $titleTranslated)
    {

    }
}
