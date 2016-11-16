<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use CultuurNet\UDB3\Label;

class AddLabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_derives_from_abstract_label_command()
    {
        $addLabel = new AddLabel('organizerId', new Label('foo'));

        $this->assertInstanceOf(AbstractLabelCommand::class, $addLabel);
    }
}
