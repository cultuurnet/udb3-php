<?php

/**
 * @file
 * Contains CultuurNet\UDB3\BookingInfoUpdatedTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Trait for the DescriptionUpdated events.
 */
trait BookingInfoUpdatedTrait
{
    /**
     * The new booking Info.
     * @var string
     */
    protected $bookingInfo;

    /**
     * @return string
     */
    public function getBookingInfo()
    {
        return $this->bookingInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'bookingInfo' => $this->bookingInfo,
        );
    }
}
