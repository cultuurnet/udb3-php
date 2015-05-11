<?php

namespace CultuurNet\UDB3\StringFilter;

class TruncateStringFilterTest extends StringFilterTest
{
    /**
     * @var TruncateStringFilter
     */
    protected $filter;

    /**
     * Returns the filter to be used in all the test methods of the test.
     * @return TruncateStringFilter
     */
    protected function getFilter()
    {
        return new TruncateStringFilter(15);
    }

    /**
     * @test
     */
    public function it_truncates_strings()
    {
        // String longer than the allowed character count.
        $original = 'Sem Dolor Tristique Mollis Fusce Mollis Nibh Aenean Tortor Consectetur.';
        $expected = 'Sem Dolor Trist';
        $this->assertFilterValue($expected, $original);

        // String shorter than the allowed character count.
        $original = 'Sem';
        $expected = 'Sem';
        $this->assertFilterValue($expected, $original);
    }

    /**
     * @test
     */
    public function it_can_truncate_words_safely()
    {
        // Basic word-safe truncating.
        $this->filter->turnOnWordSafe();
        $original = 'Sem Dolor Tristique Mollis Fusce Mollis Nibh Aenean Tortor Consectetur.';
        $expected = 'Sem Dolor';
        $this->assertFilterValue($expected, $original);

        // Don't attempt word-safe truncating if the string is not as long as the minimum word-safe character count.
        $this->filter->turnOnWordSafe(300);
        $original = 'Sem Dolor Tristique Mollis Fusce Mollis Nibh Aenean Tortor Consectetur.';
        $expected = 'Sem Dolor Trist';
        $this->assertFilterValue($expected, $original);
    }

    /**
     * @test
     */
    public function it_can_add_an_ellipsis()
    {
        $this->filter->addEllipsis(true);

        // String shorter than the allowed character count should not be suffixed with an ellipsis.
        $original = 'Sem';
        $expected = 'Sem';
        $this->assertFilterValue($expected, $original);

        // Basic ellipsis behavior.
        $original = 'Sem Dolor Tristique Mollis Fusce Mollis Nibh Aenean Tortor Consectetur.';
        $expected = 'Sem Dolor Tr...';
        $this->assertFilterValue($expected, $original);

        // Trim dots when adding an ellipsis.
        $original = 'Sem Dolor I. Tristique Mollis Fusce Mollis Nibh Aenean Tortor Consectetur.';
        $expected = 'Sem Dolor I...';
        $this->assertFilterValue($expected, $original);

        // Word-safe truncating doesn't break using an ellipsis.
        $this->filter->turnOnWordSafe();
        $original = 'Sem Dolor Tristique Mollis Fusce Mollis Nibh Aenean Tortor Consectetur.';
        $expected = 'Sem Dolor...';
        $this->assertFilterValue($expected, $original);

        // Ellipsis is truncated if maximum length is very small.
        $this->filter->setMaxLength(2);
        $original = 'Sem';
        $expected = '..';
        $this->assertFilterValue($expected, $original);
    }

    /**
     * @test
     */
    public function it_does_not_truncate_new_lines_when_word_safe_is_on()
    {
        $this->filter->turnOnWordSafe(0);
        $expected = "Wij\n zijn";
        $original = "Wij\n zijn Murgawawa çava, een vrolijke groep";
        $this->assertFilterValue($expected, $original);
    }
}
