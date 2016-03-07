<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\AddLabelToQuery;

class AddLabelToQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddLabelToQuery
     */
    protected $labelQuery;

    public function setUp()
    {
        $this->labelQuery = new AddLabelToQuery(
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
