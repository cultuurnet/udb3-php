<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\BookingInfo;

class UpdateBookingInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateBookingInfo
     */
    protected $updateBookingInfo;

    public function setUp()
    {
        $this->updateBookingInfo = new UpdateBookingInfo(
            'id',
            new BookingInfo(
                'http://foo.bar',
                'urlLabel',
                '0123456789',
                'foo@bar.com',
                '01/01/2016',
                '31/01/2016',
                'name',
                'description'
            )
        );
    }

    /**
     * @test
     */
    public function it_is_possible_to_instantiate_the_command_with_parameters()
    {
        $expectedUpdateBookingInfo = new UpdateBookingInfo(
            'id',
            new BookingInfo(
                'http://foo.bar',
                'urlLabel',
                '0123456789',
                'foo@bar.com',
                '01/01/2016',
                '31/01/2016',
                'name',
                'description'
            )
        );

        $this->assertEquals($expectedUpdateBookingInfo, $this->updateBookingInfo);
    }
}
