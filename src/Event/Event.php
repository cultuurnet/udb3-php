<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Language;

class Event extends EventSourcedAggregateRoot
{
    protected $eventId;
    protected $labels = array();

    /**
     * Factory method to create a new event.
     *
     * @param string $eventId
     * @param Title $title
     * @param string $location
     * @param \DateTime $date
     * @return Event
     */
    public static function create($eventId, Title $title, $location, \DateTime $date, EventType $type)
    {
        $event = new self();
        $event->apply(new EventCreated($eventId, $title, $location, $date, $type));

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

    public function getLabels()
    {
        return $this->labels;
    }

    public function label(Label $label)
    {
        $newLabel = (string) $label;
        $similarLabels = array_filter($this->labels, function ($label) use ($newLabel) {
            return strcasecmp($label, $newLabel) == 0;
        });

        if (!empty($similarLabels)) {
            return;
        }

        $this->apply(new EventWasLabelled($this->eventId, $label));
    }

    public function unlabel(Label $label)
    {
        if (!in_array($label, $this->labels)) {
            return;
        }
        $this->apply(new Unlabelled($this->eventId, $label));
    }

    protected function applyEventCreated(EventCreated $eventCreated)
    {
        $this->eventId = $eventCreated->getEventId();
    }

    protected function applyEventWasLabelled(EventWasLabelled $eventLabelled)
    {
        $newLabel = $eventLabelled->getLabel();
        $similarLabels = array_filter($this->labels, function ($label) use ($newLabel) {
            return strcasecmp($label, $newLabel) == 0;
        });

        if (empty($similarLabels)) {
            $this->labels[] = $eventLabelled->getLabel();
        }
    }

    protected function applyUnlabelled(Unlabelled $unlabelled)
    {
        $this->labels = array_filter(
            $this->labels,
            function (Label $label) use ($unlabelled) {
                return $label != $unlabelled->getLabel();
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

        $this->labels = array();
        foreach (array_values($udb2Event->getKeywords()) as $udb2Keyword) {
            $keyword = trim($udb2Keyword);
            if ($keyword) {
                $this->labels[] = new Label($keyword);
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

    public function updateWithCdbXml($cdbXml, $cdbXmlNamespaceUri)
    {
        $this->apply(
            new EventUpdatedFromUDB2(
                $this->eventId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );
    }
}
