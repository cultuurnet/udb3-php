<?php

namespace CultuurNet\UDB3\Place;

use Broadway\Repository\AggregateNotFoundException;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Offer\DefaultOfferEditingService;
use CultuurNet\UDB3\OfferEditingInterface;
use CultuurNet\UDB3\Place\Commands\DeletePlace;
use CultuurNet\UDB3\Place\Commands\UpdateFacilities;
use CultuurNet\UDB3\Place\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;

class DefaultPlaceEditingService extends DefaultOfferEditingService implements
    PlaceEditingServiceInterface,
    OfferEditingInterface
{
    use \CultuurNet\UDB3\OfferEditingTrait;

    /**
     * {@inheritdoc}
     */
    public function createPlace(Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, Theme $theme = null)
    {
        $id = $this->uuidGenerator->generate();

        $place = Place::createPlace($id, $title, $eventType, $address, $calendar, $theme);

        $this->offerRepository->save($place);

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMajorInfo($id, Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, $theme = null)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            new UpdateMajorInfo($id, $title, $eventType, $address, $calendar, $theme)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deletePlace($id)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            new DeletePlace($id)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateFacilities($id, array $facilities)
    {

        $this->guardId($id);

        return $this->commandBus->dispatch(
            new UpdateFacilities($id, $facilities)
        );
    }

    /**
     * @param string $id
     * @throws AggregateNotFoundException
     * @return Place
     */
    public function guardId($id)
    {
        // This validates if the id is valid.
        return $this->offerRepository->load($id);
    }
}
