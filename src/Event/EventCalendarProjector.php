<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventHandling\EventListenerInterface;
use \CultureFeed_Cdb_Item_Event as Event;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventCdbXMLInterface;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\ReadModel\CacheCalendarRepository;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;

class EventCalendarProjector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var CacheCalendarRepository
     */
    protected $repository;

    /**
     * @param CacheCalendarRepository $repository
     */
    public function __construct(CacheCalendarRepository $repository)
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
     * @param EventCdbXMLInterface $event
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
