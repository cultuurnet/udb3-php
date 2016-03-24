<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Events;

class ContentTypes
{
    const MAP = [
        BookingInfoUpdated::class => 'application/vnd.cultuurnet.udb3-events.event-booking-info-updated+json',
        CollaborationDataAdded::class => 'application/vnd.cultuurnet.udb3-events.event-collaboration-data-added+json',
        ContactPointUpdated::class => 'application/vnd.cultuurnet.udb3-events.event-contact-point-updated+json',
        DescriptionTranslated::class => 'application/vnd.cultuurnet.udb3-events.event-description-translated+json',
        DescriptionUpdated::class => 'application/vnd.cultuurnet.udb3-events.event-description-updated+json',
        EventCreated::class => 'application/vnd.cultuurnet.udb3-events.event-created+json',
        EventCreatedFromCdbXml::class => 'application/vnd.cultuurnet.udb3-events.event-created-from-cdbxml+json',
        EventDeleted::class => 'application/vnd.cultuurnet.udb3-events.event-deleted+json',
        EventImportedFromUDB2::class => 'application/vnd.cultuurnet.udb3-events.event-imported-from-udb2+json',
        EventUpdatedFromCdbXml::class => 'application/vnd.cultuurnet.udb3-events.event-updated-from_cdbxml+json',
        EventUpdatedFromUDB2::class => 'application/vnd.cultuurnet.udb3-events.event-updated-from_udb2+json',
        ImageAdded::class => 'application/vnd.cultuurnet.udb3-events.event-image-added+json',
        ImageRemoved::class => 'application/vnd.cultuurnet.udb3-events.event-image-removed+json',
        ImageUpdated::class => 'application/vnd.cultuurnet.udb3-events.event-image-updated+json',
        LabelAdded::class => 'application/vnd.cultuurnet.udb3-events.event-label-added+json',
        LabelDeleted::class => 'application/vnd.cultuurnet.udb3-events.event-label-deleted+json',
        LabelsMerged::class => 'application/vnd.cultuurnet.udb3-events.event-labels-merged+json',
        MainImageSelected::class => 'application/vnd.cultuurnet.udb3-events.event-main-image-selected+json',
        MajorInfoUpdated::class => 'application/vnd.cultuurnet.udb3-events.event-major-info-updated+json',
        OrganizerDeleted::class => 'application/vnd.cultuurnet.udb3-events.event-organizer-deleted+json',
        OrganizerUpdated::class => 'application/vnd.cultuurnet.udb3-events.event-organizer-updated+json',
        TitleTranslated::class => 'application/vnd.cultuurnet.udb3-events.event-title-translated+json',
        TranslationApplied::class => 'application/vnd.cultuurnet.udb3-events.event-translation-applied+json',
        TranslationDeleted::class => 'application/vnd.cultuurnet.udb3-events.event-translation-deleted+json',
        TypicalAgeRangeUpdated::class => 'application/vnd.cultuurnet.udb3-events.event-typical-age-range-updated+json',
        TypicalAgeRangeDeleted::class => 'application/vnd.cultuurnet.udb3-events.event-typical-age-range-deleted+json',
    ];

    /**
     * Intentionally made private.
     */
    private function __construct()
    {

    }
}
