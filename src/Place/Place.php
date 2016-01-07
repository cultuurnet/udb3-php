<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\Place.
 */

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Actor\Actor;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Cdb\UpdateableWithCdbXmlInterface;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\MediaObject;
use CultuurNet\UDB3\Place\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Place\Events\ContactPointUpdated;
use CultuurNet\UDB3\Place\Events\DescriptionUpdated;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\ImageAdded;
use CultuurNet\UDB3\Place\Events\ImageDeleted;
use CultuurNet\UDB3\Place\Events\ImageUpdated;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\OrganizerDeleted;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use Symfony\Component\EventDispatcher\Event;
use ValueObjects\String\String;

class Place extends Actor implements UpdateableWithCdbXmlInterface
{
    /**
     * The actor id.
     *
     * @var string
     */
    protected $actorId;

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->actorId;
    }

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
    public static function createPlace($id, Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, Theme $theme = null)
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

    public function deleteTypicalAgeRange()
    {
        $this->apply(new TypicalAgeRangeDeleted($this->actorId));
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
     * Updated the contact point.
     *
     * @param ContactPoint $contactPoint
     */
    public function updateContactPoint(ContactPoint $contactPoint)
    {
        $this->apply(new ContactPointUpdated($this->actorId, $contactPoint));
    }

    /**
     * Updated the booking info.
     *
     * @param BookingInfo $bookingInfo
     */
    public function updateBookingInfo(BookingInfo $bookingInfo)
    {
        $this->apply(new BookingInfoUpdated($this->actorId, $bookingInfo));
    }

    /**
     * Update the facilities.
     *
     * @param array $facilities
     */
    public function updateFacilities(array $facilities)
    {
        $this->apply(new FacilitiesUpdated($this->actorId, $facilities));
    }

    /**
     * Add a new image.
     *
     * @param MediaObject $mediaObject
     */
    public function addImage(MediaObject $mediaObject)
    {
        $this->apply(new ImageAdded($this->actorId, $mediaObject));
    }

    /**
     * Update an image.
     *
     * @param int $indexToUpdate
     * @param MediaObject $mediaObject
     */
    public function updateImage($indexToUpdate, MediaObject $mediaObject)
    {
        $this->apply(new ImageUpdated($this->actorId, $indexToUpdate, $mediaObject));
    }

    /**
     * Delet an image.
     *
     * @param int $indexToDelete
     * @param mixed int|string $internalId
     */
    public function deleteImage($indexToDelete, $internalId)
    {
        $this->apply(new ImageDeleted($this->actorId, $indexToDelete, $internalId));
    }

    /**
     * Update the major info.
     *
     * @param Title $title
     * @param EventType $eventType
     * @param Address $address
     * @param CalendarInterface $calendar
     * @param type $theme
     */
    public function updateMajorInfo(Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, $theme = null)
    {
        $this->apply(new MajorInfoUpdated($this->actorId, $title, $eventType, $address, $calendar, $theme));
    }

    /**
     * Delete this item.
     */
    public function deletePlace()
    {
        $this->apply(new PlaceDeleted($this->actorId));
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

    /**
     * @inheritdoc
     */
    public function updateWithCdbXml($cdbXml, $cdbXmlNamespaceUri)
    {
        $this->apply(
            new PlaceUpdatedFromUDB2(
                $this->actorId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );
    }
}
