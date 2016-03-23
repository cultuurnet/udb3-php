<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;

class AddLabelToMultipleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddLabelToMultiple
     */
    protected $labelMultiple;

    /**
     * @var OfferIdentifierCollection
     */
    protected $offerIdentifiers;

    /**
     * @var Label
     */
    protected $label;

    public function setUp()
    {
        $this->offerIdentifiers = OfferIdentifierCollection::fromArray(
            [
                new IriOfferIdentifier(
                    'event/1',
                    '1',
                    OfferType::EVENT()
                ),
                new IriOfferIdentifier(
                    'event/2',
                    '2',
                    OfferType::EVENT()
                ),
                new IriOfferIdentifier(
                    'event/3',
                    '3',
                    OfferType::EVENT()
                )
            ]
        );

        $this->label = new Label('testlabel');

        $this->labelMultiple = new AddLabelToMultiple(
            $this->offerIdentifiers,
            $this->label
        );
    }

    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $this->assertEquals($this->offerIdentifiers, $this->labelMultiple->getOfferIdentifiers());
        $this->assertEquals($this->label, $this->labelMultiple->getLabel());
    }
}
