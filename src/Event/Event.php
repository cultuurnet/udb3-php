<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Language;

class Event extends EventSourcedAggregateRoot
{
    protected $eventId;
    protected $keywords = array();

    /**
     * Factory method to create a new event.
     *
     * @param string $eventId
     * @param string $title
     * @param string $location
     * @param string $date
     * @return Event
     */
    public static function create($eventId, $title, $location, $date)
    {
        $event = new self();
        $event->apply(new EventCreated($eventId, $title, $location, $date));

        return $event;
    }

    /**
     * @param string $eventId
     * @param string $cdbXml
     * @param string $cdbXmlNamespaceUri
     * @return Event
     */
    public static function importFromUDB2(
        $eventId,
        $cdbXml,
        $cdbXmlNamespaceUri
    ) {
        $event = new self();
        $event->apply(
            new EventImportedFromUDB2(
                $eventId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );

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

    public function tag(Keyword $keyword)
    {
        if (in_array($keyword, $this->keywords)) {
            return;
        }

        $this->apply(new EventWasTagged($this->eventId, $keyword));
    }

    public function eraseTag(Keyword $keyword)
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
                return $keyword != $tagErased->getKeyword();
            }
        );
    }

    protected function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImported
    ) {
        $this->eventId = $eventImported->getEventId();

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImported->getCdbXmlNamespaceUri(),
            $eventImported->getCdbXml()
        );

        $this->keywords = array();
        foreach (array_values($udb2Event->getKeywords()) as $udb2Keyword) {
            $keyword = trim($udb2Keyword);
            if ($keyword) {
                $this->keywords[] = new Keyword($keyword);
            }
        }
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
        $this->apply(
            new DescriptionTranslated($this->eventId, $language, $description)
        );
    }

    protected function applyTitleTranslated(TitleTranslated $titleTranslated)
    {
    }
}
