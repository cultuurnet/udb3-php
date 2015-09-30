<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Cdb\Event;

use CultureFeed_Cdb_Item_Event;
use PHPUnit_Framework_TestCase;

class NotTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_negates_another_specification()
    {
        $otherSpec = $this->getMock(SpecificationInterface::class);

        $event = new CultureFeed_Cdb_Item_Event();

        $otherSpec->expects($this->at(0))
            ->method('isSatisfiedByEvent')
            ->with($event)
            ->willReturn(true);

        $otherSpec->expects($this->at(1))
            ->method('isSatisfiedByEvent')
            ->with($event)
            ->willReturn(false);

        $spec = new Not($otherSpec);


        $this->assertEquals(
            false,
            $spec->isSatisfiedByEvent($event)
        );

        $this->assertEquals(
            true,
            $spec->isSatisfiedByEvent($event)
        );
    }
}
