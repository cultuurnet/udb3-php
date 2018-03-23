<?php

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Model\ValueObject\Contact\BookingAvailability;
use CultuurNet\UDB3\Model\ValueObject\Contact\TelephoneNumber;
use CultuurNet\UDB3\Model\ValueObject\Web\EmailAddress;
use CultuurNet\UDB3\Model\ValueObject\Web\TranslatedWebsiteLabel;
use CultuurNet\UDB3\Model\ValueObject\Web\Url;
use CultuurNet\UDB3\Model\ValueObject\Web\WebsiteLabel;
use CultuurNet\UDB3\Model\ValueObject\Web\WebsiteLink;

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

    /**
     * @test
     */
    public function it_should_be_creatable_from_a_complete_udb3_model_booking_info()
    {
        $udb3ModelBookingInfo = new \CultuurNet\UDB3\Model\ValueObject\Contact\BookingInfo(
            new WebsiteLink(
                new Url('https://publiq.be'),
                new TranslatedWebsiteLabel(
                    new \CultuurNet\UDB3\Model\ValueObject\Translation\Language('nl'),
                    new WebsiteLabel('Publiq')
                )
            ),
            new TelephoneNumber('044/444444'),
            new EmailAddress('info@publiq.be'),
            new BookingAvailability(
                \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2018-01-01T00:00:00+01:00'),
                \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2018-01-10T00:00:00+01:00')
            )
        );

        $expected = new BookingInfo(
            'https://publiq.be',
            'Publiq',
            '044/444444',
            'info@publiq.be',
            '2018-01-01T00:00:00+01:00',
            '2018-01-10T00:00:00+01:00'
        );

        $actual = BookingInfo::fromUdb3ModelBookingInfo($udb3ModelBookingInfo);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_should_be_creatable_from_an_empty_udb3_model_booking_info()
    {
        $udb3ModelBookingInfo = new \CultuurNet\UDB3\Model\ValueObject\Contact\BookingInfo();

        $expected = new BookingInfo();
        $actual = BookingInfo::fromUdb3ModelBookingInfo($udb3ModelBookingInfo);

        $this->assertEquals($expected, $actual);
    }
}
