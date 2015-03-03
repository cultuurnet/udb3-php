<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Title;

class TitleTest extends \PHPUnit_Framework_TestCase
{
    public function emptyStringValues()
    {
        return array(
            array(''),
            array(' '),
            array('   '),
        );
    }

    /**
     * @test
     * @dataProvider emptyStringValues()
     */
    public function it_can_not_be_empty($emptyStringValue)
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Title can not be empty.'
        );
        new Title($emptyStringValue);
    }
}
