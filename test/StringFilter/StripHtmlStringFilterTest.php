<?php

namespace CultuurNet\UDB3\StringFilter;

class StripHtmlStringFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_strips_html_tags()
    {
        $original = '<span>Lorem ipsum</span> <strong>dolor</strong>';
        $expected = 'Lorem ipsum dolor';

        $filter = new StripHtmlStringFilter();
        $actual = $filter->filter($original);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     *
     * @dataProvider newlineDataProvider
     */
    public function it_converts_paragraphs_and_breaks_into_newlines($original, $expected)
    {
        $filter = new StripHtmlStringFilter();
        $actual = $filter->filter($original);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provides test data for converting paragraphs and breaks into newlines.
     *
     * @return array
     *   Array of arrays, each individual array contains all arguments for the test method.
     */
    public function newlineDataProvider()
    {
        $single_newline = 'Line 1.' . PHP_EOL . 'Line 2.';
        $double_newline = 'Line 1.' . PHP_EOL . PHP_EOL . 'Line 2.';
        return array(
            array('<p>Line 1.</p><p>Line 2.</p>', $single_newline),
            array('Line 1.<br />Line 2.<br />', $single_newline),
            array('<p>Line 1.</p><br /><p>Line 2.</p><br /><br />', $double_newline),
            array('<br />Line 1.<br /><br />Line 2.<br />', $double_newline),
        );
    }
}