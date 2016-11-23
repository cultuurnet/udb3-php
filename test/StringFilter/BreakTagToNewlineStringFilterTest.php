<?php

namespace CultuurNet\UDB3\StringFilter;

class BreakTagToNewlineStringFilterTest extends StringFilterTest
{
    /**
     * @return BreakTagToNewlineStringFilter
     */
    protected function getFilter()
    {
        return new BreakTagToNewlineStringFilter();
    }

    /**
     * @test
     */
    public function it_converts_newlines_to_break_tags()
    {
        $original = "Hello<br>world!<br/>Goodbye!<br />Nice to have known you!";
        $expected = "Hello\nworld!\nGoodbye!\nNice to have known you!";
        $this->assertFilterValue($expected, $original);
    }

    /**
     * @test
     */
    public function it_converts_consecutive_newlines_to_consecutive_break_tags()
    {
        $original = "Hello<br /><br />world!";
        $expected = "Hello\n\nworld!";
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
