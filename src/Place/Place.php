<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\Place.
 */

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Actor\Actor;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Place\Events\DescriptionUpdated;
use CultuurNet\UDB3\Place\Events\OrganizerDeleted;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Title;
use Symfony\Component\EventDispatcher\Event;
use ValueObjects\String\String;

class Place extends Actor
{

    /**
     * Factory method to create a new Place.
     *
     * @todo Refactor this method so it can be called create. Currently the
     * normal behavior for create is taken by the legacy udb2 logic.
     * The PlaceImportedFromUDB2 could be a superclass of Place.
     *
     * @param String $id
     * @param Title $title
     * @param EventType $eventType
     * @param Address $address
     * @param CalendarInterface $calendar
     * @param Theme/null $theme
     *
     * @return Event
     */
    public static function createPlace($id, Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, $theme = null)
    {
        $place = new self();
        $place->apply(new PlaceCreated($id, $title, $eventType, $address, $calendar, $theme));

        return $place;
    }

    /**
     * Apply the place created event.
     * @param PlaceCreate $placeCreated
     */
    protected function applyPlaceCreated(PlaceCreated $placeCreated)
    {
        $this->actorId = $placeCreated->getPlaceId();
    }

    /**
     * @param string $description
     */
    public function updateDescription($description)
    {
        $this->apply(new DescriptionUpdated($this->actorId, $description));
    }

    /**
     * @param string $typicalAgeRange
     */
    public function updateTypicalAgeRange($typicalAgeRange)
    {
        $this->apply(new TypicalAgeRangeUpdated($this->actorId, $typicalAgeRange));
    }

    /**
     * Handle an update command to update organizer.
     */
    public function updateOrganizer($organizerId)
    {
        $this->apply(new OrganizerUpdated($this->actorId, $organizerId));
    }

    /**
     * Delete the given organizer.
     *
     * @param string $organizerId
     */
    public function deleteOrganizer($organizerId)
    {
        $this->apply(new OrganizerDeleted($this->actorId, $organizerId));
    }

    /**
     * Import from UDB2.
     *
     * @param string $actorId
     *   The actor id.
     * @param string $cdbXml
     *   The cdb xml.
     * @param string $cdbXmlNamespaceUri
     *   The cdb xml namespace uri.
     *
     * @return Actor
     *   The actor.
     */
    public static function importFromUDB2(
        $actorId,
        $cdbXml,
        $cdbXmlNamespaceUri
    ) {
        $place = new static();
        $place->apply(
            new PlaceImportedFromUDB2(
                $actorId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );

        return $place;
    }

    public function applyPlaceImportedFromUDB2(
        PlaceImportedFromUDB2 $placeImported
    ) {
        $this->applyActorImportedFromUDB2($placeImported);
    }
}
