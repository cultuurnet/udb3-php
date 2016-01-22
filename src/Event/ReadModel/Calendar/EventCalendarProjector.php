<?php

namespace CultuurNet\UDB3\Event\ReadModel\Calendar;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventCdbXMLInterface;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;

class EventCalendarProjector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var CalendarRepositoryInterface
     */
    protected $repository;

    /**
     * @param CalendarRepositoryInterface $repository
     */
    public function __construct(CalendarRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    public function applyEventImportedFromUDB2(EventImportedFromUDB2 $eventImportedFromUDB2)
    {
        $this->saveEventCalendar($eventImportedFromUDB2);
    }

    /**
     * @param EventUpdatedFromUDB2 $eventUpdatedFromUDB2
     */
    public function applyEventUpdatedFromUDB2(EventUpdatedFromUDB2 $eventUpdatedFromUDB2)
    {
        $this->saveEventCalendar($eventUpdatedFromUDB2);
    }

    /**
     * @param EventCreatedFromCdbXml $eventCreatedFromCdbXml
     */
    public function applyEventCreatedFromCdbXml(EventCreatedFromCdbXml $eventCreatedFromCdbXml)
    {
        $eventId = $eventCreatedFromCdbXml->getEventId();

        $event = EventItemFactory::createEventFromCdbXml(
            $eventCreatedFromCdbXml->getCdbXmlNamespaceUri()->toNative(),
            $eventCreatedFromCdbXml->getEventXmlString()->toEventXmlString()
        );

        $this->repository->save($eventId, $event->getCalendar());
    }

    /**
     * @param EventUpdatedFromCdbXml $eventUpdatedFromCdbXml
     */
    public function applyEventUpdatedFromCdbXml(EventUpdatedFromCdbXml $eventUpdatedFromCdbXml)
    {
        $eventId = $eventUpdatedFromCdbXml->getEventId();

        $event = EventItemFactory::createEventFromCdbXml(
            $eventUpdatedFromCdbXml->getCdbXmlNamespaceUri()->toNative(),
            $eventUpdatedFromCdbXml->getEventXmlString()->toEventXmlString()
        );

        $this->repository->save($eventId, $event->getCalendar());
    }

    /**
     * @param EventCdbXMLInterface $eventEvent
     */
    private function saveEventCalendar(EventCdbXMLInterface $eventEvent)
    {
        $eventId = $eventEvent->getEventId();

        $event = EventItemFactory::createEventFromCdbXml(
            $eventEvent->getCdbXmlNamespaceUri(),
            $eventEvent->getCdbXml()
        );

        $this->repository->save($eventId, $event->getCalendar());
    }
}
