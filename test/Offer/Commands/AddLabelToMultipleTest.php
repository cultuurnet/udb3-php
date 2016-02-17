<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\Entry\Keyword;

class AddLabelToMultipleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddLabelToMultiple
     */
    protected $labelMultiple;

    public function setUp()
    {
        $this->labelMultiple = new AddLabelToMultiple(
            array('id1', 'id2', 'id3'),
            new Keyword('testlabel')
        );
    }

    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedIds = array('id1', 'id2', 'id3');
        $expectedKeyword = new Keyword('testlabel');

        $this->assertEquals($expectedIds, $this->labelMultiple->getOfferIds());
        $this->assertEquals($expectedKeyword, $this->labelMultiple->getLabel());
    }
}
