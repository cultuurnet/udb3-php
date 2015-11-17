<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

class LabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_refuses_value_that_are_not_strings()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        new Label(null);
    }

    /**
     * @test
     */
    public function it_refuses_visible_that_is_not_a_boolean()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        new Label('keyword 1', null);
    }
}
