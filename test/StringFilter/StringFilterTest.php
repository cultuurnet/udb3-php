<?php

namespace CultuurNet\UDB3\StringFilter;

abstract class StringFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StringFilterInterface
     */
    protected $filter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->filter = $this->getFilter();
    }

    /**
     * Returns the filter to be used in all the test methods of the test.
     * @return StringFilterInterface
     */
    abstract protected function getFilter();

    /**
     * Uses the $filter property to filter a string.
     */
    protected function filter($string)
    {
        return $this->filter->filter($string);
    }

    /**
     * Asserts that a filtered string is the same as a another string.
     *
     * @param string $expected
     *   Expected string value after filtering.
     * @param string $original
     *   String to filter and compare afterwards.
     */
    protected function assertFilterValue($expected, $original)
    {
        $actual = $this->filter($original);
        $this->assertEquals($expected, $actual);
    }
}