<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

class CalendarTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function it_validates_calendar_type()
    {
        $calendar = new Calendar('unknown');
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function it_validates_start_date()
    {
        $calendar = new Calendar('multiple');
    }
}
