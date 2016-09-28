<?php

namespace CultuurNet\UDB3\Organizer\Events;

use ValueObjects\Identity\UUID;

class LabelAddedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_derives_from_abstract_label_event()
    {
        $labelAdded = new LabelAdded('organizerId', new UUID());

        $this->assertInstanceOf(AbstractLabelEvent::class, $labelAdded);
    }
}
