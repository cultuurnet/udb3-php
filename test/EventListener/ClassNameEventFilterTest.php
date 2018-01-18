<?php

namespace CultuurNet\UDB3\EventListener;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelRemoved;
use ValueObjects\StringLiteral\StringLiteral;

class ClassNameEventFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LabelAdded
     */
    private $labelAdded;

    protected function setUp()
    {
        $this->labelAdded = new LabelAdded(
            '26e36905-64d0-4cac-ba41-6d6dcd997ca0',
            new Label('UiTPAS')
        );
    }

    /**
     * @test
     */
    public function it_returns_true_when_class_name_matches()
    {
        $classNameEventFilter = new ClassNameEventFilter(
            new StringLiteral(LabelAdded::class),
            new StringLiteral(LabelRemoved::class)
        );

        $this->assertTrue($classNameEventFilter->matches($this->labelAdded));
    }

    /**
     * @test
     */
    public function it_returns_false_when_class_name_does_not_match()
    {
        $classNameEventFilter = new ClassNameEventFilter(
            new StringLiteral(LabelRemoved::class)
        );

        $this->assertFalse($classNameEventFilter->matches($this->labelAdded));
    }
}
