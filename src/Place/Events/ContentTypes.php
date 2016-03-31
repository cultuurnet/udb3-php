<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\Events;

class ContentTypes
{
    /**
     * Intentionally made private.
     */
    private function __construct()
    {

    }

    /**
     * @return array
     *
     * @todo once we upgrade to PHP 5.6+ this can be moved to a constant.
     */
    public static function map()
    {
        return [
            BookingInfoUpdated::class => 'application/vnd.cultuurnet.udb3-events.place-booking-info-updated+json',
            ContactPointUpdated::class => 'application/vnd.cultuurnet.udb3-events.place-contact-point-updated+json',
            DescriptionTranslated::class => 'application/vnd.cultuurnet.udb3-events.place-description-translated+json',
            DescriptionUpdated::class => 'application/vnd.cultuurnet.udb3-events.place-description-updated+json',
            FacilitiesUpdated::class => 'application/vnd.cultuurnet.udb3-events.place-facilities-updated+json',
            ImageAdded::class => 'application/vnd.cultuurnet.udb3-events.place-image-added+json',
            ImageRemoved::class => 'application/vnd.cultuurnet.udb3-events.place-image-removed+json',
            ImageUpdated::class => 'application/vnd.cultuurnet.udb3-events.place-image-updated+json',
            LabelAdded::class => 'application/vnd.cultuurnet.udb3-events.place-label-added+json',
            LabelDeleted::class => 'application/vnd.cultuurnet.udb3-events.place-label-deleted+json',
            MainImageSelected::class => 'application/vnd.cultuurnet.udb3-events.place-main-image-selected+json',
            MajorInfoUpdated::class => 'application/vnd.cultuurnet.udb3-events.place-major-info-updated+json',
            OrganizerDeleted::class => 'application/vnd.cultuurnet.udb3-events.place-organizer-deleted+json',
            OrganizerUpdated::class => 'application/vnd.cultuurnet.udb3-events.place-organizer-updated+json',
            PlaceCreated::class => 'application/vnd.cultuurnet.udb3-events.place-created+json',
            PlaceDeleted::class => 'application/vnd.cultuurnet.udb3-events.place-deleted+json',
            PlaceImportedFromUDB2::class => 'application/vnd.cultuurnet.udb3-events.place-imported-from-udb2+json',
            PlaceImportedFromUDB2Event::class => 'application/vnd.cultuurnet.udb3-events.place-imported-from-udb2-event+json',
            PlaceUpdatedFromUDB2::class => 'application/vnd.cultuurnet.udb3-events.place-updated-from-udb2+json',
            TitleTranslated::class => 'application/vnd.cultuurnet.udb3-events.place-title-translated+json',
            TypicalAgeRangeUpdated::class => 'application/vnd.cultuurnet.udb3-events.place-typical-age-range-updated+json',
            TypicalAgeRangeDeleted::class => 'application/vnd.cultuurnet.udb3-events.place-typical-age-range-deleted+json',
        ];
    }
}
