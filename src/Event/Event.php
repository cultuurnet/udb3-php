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
use CultuurNet\UDB3\XmlString;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use ValueObjects\String\String;

class Event extends EventSourcedAggregateRoot
{
    protected $eventId;

    /**
     * @var Label[]
     */
    protected $labels = [];

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
        if (!is_string($eventId)) {
            throw new \InvalidArgumentException(
                'Expected eventId to be a string, received ' . gettype($eventId)
            );
        }
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
     * @param XmlString $xmlString
     * @param String $eventId
     * @return Event
     */
    public static function createFromCdbXml(String $eventId, XmlString $xmlString)
    {
        $event = new self();
        $event->apply(
            new EventCreatedFromCdbXml(
                $eventId,
                $xmlString
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

    /**
     * @param Label $label
     * @return bool
     */
    private function hasLabel(Label $label)
    {
        $equalLabels = array_filter(
            $this->labels,
            function (Label $existingLabel) use ($label) {
                return $label->equals($existingLabel);
            }
        );

        return !empty($equalLabels);
    }

    /**
     * @param Label $label
     */
    public function label(Label $label)
    {
        if (!$this->hasLabel($label)) {
            $this->apply(new EventWasLabelled($this->eventId, $label));
        }
    }

    /**
     * @param Label $label
     */
    public function unlabel(Label $label)
    {
        if ($this->hasLabel($label)) {
            $this->apply(new Unlabelled($this->eventId, $label));
        }
    }

    protected function applyEventCreated(EventCreated $eventCreated)
    {
        $this->eventId = $eventCreated->getEventId();
    }

    protected function applyEventWasLabelled(EventWasLabelled $eventLabelled)
    {
        $newLabel = $eventLabelled->getLabel();

        if (!$this->hasLabel($newLabel)) {
            $this->labels[] = $newLabel;
        }
    }

    protected function applyUnlabelled(Unlabelled $unlabelled)
    {
        $this->labels = array_filter(
            $this->labels,
            function (Label $label) use ($unlabelled) {
                return !$label->equals($unlabelled->getLabel());
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

    /**
     * @param Language $language
     * @param string $description
     */
    public function translateDescription(Language $language, $description)
    {
        $this->apply(
            new DescriptionTranslated($this->eventId, $language, $description)
        );
    }

    protected function applyTitleTranslated(TitleTranslated $titleTranslated)
    {
    }

    protected function applyEventCreatedFromCdbXml(
        EventCreatedFromCdbXml $eventCreatedFromCdbXml
    ) {
        $this->eventId = $eventCreatedFromCdbXml->getEventId()->toNative();
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
