<?php

namespace CultuurNet\UDB3\Organizer\Events;

use ValueObjects\Identity\UUID;

class LabelRemovedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_derives_from_abstract_label_event()
    {
        $labelRemoved = new LabelRemoved('organizerId', new UUID());

        $this->assertInstanceOf(AbstractLabelEvent::class, $labelRemoved);
    }
}
