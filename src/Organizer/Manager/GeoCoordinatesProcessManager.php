<?php declare(strict_types=1);

namespace CultuurNet\UDB3\Organizer\Manager;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\Organizer\Commands\UpdateGeoCoordinates;
use CultuurNet\UDB3\Organizer\Events\AddressUpdated;

class GeoCoordinatesProcessManager implements EventListenerInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();

        if ($event instanceof AddressUpdated) {
            $this->dispatchUpdateGeoCoordinatesCommand($event);
        }
    }

    public function dispatchUpdateGeoCoordinatesCommand(AddressUpdated $event): void
    {
        $this->commandBus->dispatch(
            new UpdateGeoCoordinates(
                $event->getOrganizerId(),
                $event->getAddress()
            )
        );
    }
}
