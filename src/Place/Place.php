<?php

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\UpdateableWithCdbXmlInterface;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractUpdateImage;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Offer;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Place\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Place\Events\ContactPointUpdated;
use CultuurNet\UDB3\Place\Events\DescriptionTranslated;
use CultuurNet\UDB3\Place\Events\DescriptionUpdated;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\ImageAdded;
use CultuurNet\UDB3\Place\Events\ImageRemoved;
use CultuurNet\UDB3\Place\Events\ImageUpdated;
use CultuurNet\UDB3\Place\Events\MainImageSelected;
use CultuurNet\UDB3\Place\Events\LabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\Moderation\Approved;
use CultuurNet\UDB3\Place\Events\Moderation\FlaggedAsDuplicate;
use CultuurNet\UDB3\Place\Events\Moderation\FlaggedAsInappropriate;
use CultuurNet\UDB3\Place\Events\Moderation\Rejected;
use CultuurNet\UDB3\Place\Events\OrganizerDeleted;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2Event;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;
use CultuurNet\UDB3\Place\Events\TitleTranslated;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use DateTimeImmutable;
use ValueObjects\String\String as StringLiteral;

class Place extends Offer implements UpdateableWithCdbXmlInterface
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
     * @param string $id
     * @param Title $title
     * @param EventType $eventType
     * @param Address $address
     * @param CalendarInterface $calendar
     * @param Theme|null $theme
     * @param DateTimeImmutable|null $publicationDate
     *
     * @return self
     */
    public static function createPlace(
        $id,
        Title $title,
        EventType $eventType,
        Address $address,
        CalendarInterface $calendar,
        Theme $theme = null,
        DateTimeImmutable $publicationDate = null
    ) {
        $place = new self();
        $place->apply(new PlaceCreated(
            $id,
            $title,
            $eventType,
            $address,
            $calendar,
            $theme,
            $publicationDate
        ));

        return $place;
    }

    /**
     * Apply the place created event.
     * @param PlaceCreated $placeCreated
     */
    protected function applyPlaceCreated(PlaceCreated $placeCreated)
    {
        $this->actorId = $placeCreated->getPlaceId();
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
    public static function importFromUDB2Actor(
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

    /**
     * Import from UDB2.
     *
     * @param string $placeId
     *   The actor id.
     * @param string $cdbXml
     *   The cdb xml.
     * @param string $cdbXmlNamespaceUri
     *   The cdb xml namespace uri.
     *
     * @return Place
     *   The actor.
     */
    public static function importFromUDB2Event(
        $placeId,
        $cdbXml,
        $cdbXmlNamespaceUri
    ) {
        $place = new static();
        $place->apply(
            new PlaceImportedFromUDB2Event(
                $placeId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );

        return $place;
    }

    public function applyPlaceImportedFromUDB2(
        PlaceImportedFromUDB2 $placeImported
    ) {
        $this->actorId = $placeImported->getActorId();
    }

    public function applyPlaceImportedFromUDB2Event(
        PlaceImportedFromUDB2Event $placeImported
    ) {
        $this->actorId = $placeImported->getActorId();
    }

    /**
     * @inheritdoc
     */
    public function updateWithCdbXml($cdbXml, $cdbXmlNamespaceUri)
    {
        try {
            ActorItemFactory::createActorFromCdbXml($cdbXmlNamespaceUri, $cdbXml);

            $this->apply(
                new PlaceUpdatedFromUDB2(
                    $this->actorId,
                    $cdbXml,
                    $cdbXmlNamespaceUri
                )
            );
        } catch (\CultureFeed_Cdb_ParseException $e) {
            $this->apply(
                new PlaceImportedFromUDB2Event(
                    $this->actorId,
                    $cdbXml,
                    $cdbXmlNamespaceUri
                )
            );
        }
    }

    /**
     * @param Label $label
     * @return LabelAdded
     */
    protected function createLabelAddedEvent(Label $label)
    {
        return new LabelAdded($this->actorId, $label);
    }

    /**
     * @param Label $label
     * @return LabelDeleted
     */
    protected function createLabelDeletedEvent(Label $label)
    {
        return new LabelDeleted($this->actorId, $label);
    }

    protected function createImageAddedEvent(Image $image)
    {
        return new ImageAdded($this->actorId, $image);
    }

    protected function createImageRemovedEvent(Image $image)
    {
        return new ImageRemoved($this->actorId, $image);
    }

    protected function createImageUpdatedEvent(
        AbstractUpdateImage $updateImageCommand
    ) {
        return new ImageUpdated(
            $this->actorId,
            $updateImageCommand->getMediaObjectId(),
            $updateImageCommand->getDescription(),
            $updateImageCommand->getCopyrightHolder()
        );
    }

    protected function createMainImageSelectedEvent(Image $image)
    {
        return new MainImageSelected($this->actorId, $image);
    }

    /**
     * @param Language $language
     * @param StringLiteral $title
     * @return TitleTranslated
     */
    protected function createTitleTranslatedEvent(Language $language, StringLiteral $title)
    {
        return new TitleTranslated($this->actorId, $language, $title);
    }

    /**
     * @param Language $language
     * @param StringLiteral $description
     * @return DescriptionTranslated
     */
    protected function createDescriptionTranslatedEvent(Language $language, StringLiteral $description)
    {
        return new DescriptionTranslated($this->actorId, $language, $description);
    }

    /**
     * @param string $description
     * @return DescriptionUpdated
     */
    protected function createDescriptionUpdatedEvent($description)
    {
        return new DescriptionUpdated($this->actorId, $description);
    }

    /**
     * @param string $typicalAgeRange
     * @return TypicalAgeRangeUpdated
     */
    protected function createTypicalAgeRangeUpdatedEvent($typicalAgeRange)
    {
        return new TypicalAgeRangeUpdated($this->actorId, $typicalAgeRange);
    }

    /**
     * @return TypicalAgeRangeDeleted
     */
    protected function createTypicalAgeRangeDeletedEvent()
    {
        return new TypicalAgeRangeDeleted($this->actorId);
    }

    /**
     * @param string $organizerId
     * @return OrganizerUpdated
     */
    protected function createOrganizerUpdatedEvent($organizerId)
    {
        return new OrganizerUpdated($this->actorId, $organizerId);
    }

    /**
     * @param string $organizerId
     * @return OrganizerDeleted
     */
    protected function createOrganizerDeletedEvent($organizerId)
    {
        return new OrganizerDeleted($this->actorId, $organizerId);
    }

    /**
     * @param ContactPoint $contactPoint
     * @return ContactPointUpdated
     */
    protected function createContactPointUpdatedEvent(ContactPoint $contactPoint)
    {
        return new ContactPointUpdated($this->actorId, $contactPoint);
    }

    /**
     * @param BookingInfo $bookingInfo
     * @return BookingInfoUpdated
     */
    protected function createBookingInfoUpdatedEvent(BookingInfo $bookingInfo)
    {
        return new BookingInfoUpdated($this->actorId, $bookingInfo);
    }

    /**
     * @return PlaceDeleted
     */
    protected function createOfferDeletedEvent()
    {
        return new PlaceDeleted($this->actorId);
    }

    /**
     * @inheritDoc
     */
    protected function createApprovedEvent()
    {
        return new Approved($this->actorId);
    }

    /**
     * @inheritDoc
     */
    protected function createRejectedEvent(StringLiteral $reason)
    {
        return new Rejected($this->actorId, $reason);
    }

    /**
     * @inheritDoc
     */
    protected function createFlaggedAsDuplicate()
    {
        return new FlaggedAsDuplicate($this->actorId);
    }

    /**
     * @inheritDoc
     */
    protected function createFlaggedAsInappropriate()
    {
        return new FlaggedAsInappropriate($this->actorId);
    }
}
