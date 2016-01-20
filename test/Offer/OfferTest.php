<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Offer\Item\Item;

class OfferTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Item
     */
    protected $offer;

    /**
     * @var LabelCollection
     */
    protected $labels;

    public function setUp()
    {
        $this->offer = new Item('foo');
        $this->labels = new LabelCollection();
        $this->labels->with(new Label('test'));
        $this->labels->with(new Label('label'));
        $this->labels->with(new Label('cultuurnet'));
    }

    /**
     * @test
     */
    public function it_can_set_and_return_labels()
    {
        $this->offer->addLabel(new Label('test'));
        $this->offer->addLabel(new Label('label'));
        $this->offer->addLabel(new Label('cultuurnet'));
        $labels = $this->offer->getLabels();
        $expectedLabels = $this->labels;

        $this->assertEquals($expectedLabels, $labels);
    }

    /**
     * @test
     */
    public function it_can_delete_labels()
    {
        $this->offer->addLabel(new Label('test'));
        $this->offer->addLabel(new Label('label'));
        $this->offer->addLabel(new Label('cultuurnet'));
        $this->offer->deleteLabel(new Label('label'));

        $expectedLabels = new LabelCollection();
        $expectedLabels->with(new Label('test'));
        $expectedLabels->with(new Label('cultuurnet'));

        $this->assertEquals($expectedLabels, $this->offer->getLabels());
    }
}
