<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\BookingInfo;

class AbstractUpdateBookingInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractUpdateBookingInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateBookingInfo;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var BookingInfo
     */
    protected $bookingInfo;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->bookingInfo = new BookingInfo(
            'http://foo.bar',
            'urlLabel',
            '0123456789',
            'foo@bar.com',
            '01/01/2016',
            '31/01/2016',
            'name',
            'description'
        );

        $this->updateBookingInfo = $this->getMockForAbstractClass(
            AbstractUpdateBookingInfo::class,
            array($this->itemId, $this->bookingInfo)
        );
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $bookingInfo = $this->updateBookingInfo->getBookingInfo();
        $expectedBookingInfo = new BookingInfo(
            'http://foo.bar',
            'urlLabel',
            '0123456789',
            'foo@bar.com',
            '01/01/2016',
            '31/01/2016',
            'name',
            'description'
        );

        $this->assertEquals($expectedBookingInfo, $bookingInfo);

        $itemId = $this->updateBookingInfo->getId();
        $expectedItemId = 'Foo';

        $this->assertEquals($expectedItemId, $itemId);
    }
}
