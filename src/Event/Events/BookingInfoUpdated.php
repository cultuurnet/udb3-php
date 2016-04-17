<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Offer\Events\AbstractBookingInfoUpdated;

/**
 * Description of DescriptionUpdated
 */
class BookingInfoUpdated extends AbstractBookingInfoUpdated
{
    use BackwardsCompatibleEventTrait;
}
