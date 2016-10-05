<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use ValueObjects\Identity\UUID;

class RemoveLabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_derives_from_abstract_label_command()
    {
        $removeLabel = new RemoveLabel('organizerId', new UUID());

        $this->assertInstanceOf(AbstractLabelCommand::class, $removeLabel);
    }
}
