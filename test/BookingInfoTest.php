<?php

namespace CultuurNet\UDB3;

class BookingInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_compare()
    {
        $bookingInfo = new BookingInfo(
            'www.publiq.be',
            'publiq',
            '02 123 45 67',
            'info@publiq.be'
        );

        $sameBookingInfo = new BookingInfo(
            'www.publiq.be',
            'publiq',
            '02 123 45 67',
            'info@publiq.be'
        );

        $otherBookingInfo = new BookingInfo(
            'www.2dotstwice.be',
            '2dotstwice',
            '016 12 34 56',
            'info@2dotstwice.be'
        );

        $this->assertTrue($bookingInfo->sameAs($sameBookingInfo));
        $this->assertFalse($bookingInfo->sameAs($otherBookingInfo));
    }
}
