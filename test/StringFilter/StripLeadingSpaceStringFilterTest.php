<?php

namespace CultuurNet\UDB3\StringFilter;

class StripLeadingSpaceStringFilterTest extends StringFilterTest
{
    /**
     * @return StripLeadingSpaceStringFilter
     */
    protected function getFilter()
    {
        return new StripLeadingSpaceStringFilter();
    }

    /**
     * @test
     */
    public function it_strips_leading_spaces()
    {
        $original = "   Hello!   \n       Goodbye!\n\n\n Hello again!";
        $expected = "Hello!   \nGoodbye!\n\n\nHello again!";
        $this->assertFilterValue($expected, $original);
    }

    /**
     * @test
     */
    public function it_only_filters_strings()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->filter->filter(12345);
    }
}
