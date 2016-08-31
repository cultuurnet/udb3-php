<?php

namespace CultuurNet\UDB3\Label\ValueObjects;

class LabelNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_refuses_value_that_are_not_strings()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        new LabelName(null);
    }

    /**
     * @test
     */
    public function it_refuses_value_containing_a_semicolon()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        new LabelName(';');
    }

    /**
     * @test
     */
    public function it_refuses_value_with_length_less_than_three()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        new LabelName('k');
    }

    /**
     * @test
     */
    public function it_refuses_value_with_length_longer_than_255()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        new LabelName(
            'turnip greens yarrow ricebean rutabaga endive cauliflower sea lettuce kohlrabi amaranth water spinach avocado daikon napa cabbage asparagus winter purslane kale celery potato scallion desert raisin horseradish spinach carrot soko Lotus root water spinach fennel'
        );
    }

    /**
     * @test
     */
    public function it_accepts_a_regular_string_length_for_value()
    {
        $label = new LabelName('turnip');

        $this->assertEquals($label->__toString(), 'turnip');
    }
}
