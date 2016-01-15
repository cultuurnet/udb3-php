<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Label;

class LabelQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LabelQuery
     */
    protected $labelQuery;

    public function setUp()
    {
        $this->labelQuery = new LabelQuery(
            'query',
            new Label('testlabel')
        );
    }

    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedQuery = 'query';
        $expectedLabel = new Label('testlabel');

        $this->assertEquals($expectedQuery, $this->labelQuery->getQuery());
        $this->assertEquals($expectedLabel, $this->labelQuery->getLabel());
    }
}
