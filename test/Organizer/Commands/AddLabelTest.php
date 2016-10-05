<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use ValueObjects\Identity\UUID;

class AddLabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_derives_from_abstract_label_command()
    {
        $addLabel = new AddLabel('organizerId', new UUID());

        $this->assertInstanceOf(AbstractLabelCommand::class, $addLabel);
    }
}
