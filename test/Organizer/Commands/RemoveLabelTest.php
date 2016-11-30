<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use CultuurNet\UDB3\Label;

class RemoveLabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_derives_from_abstract_label_command()
    {
        $removeLabel = new RemoveLabel('organizerId', new Label('foo'));

        $this->assertInstanceOf(AbstractLabelCommand::class, $removeLabel);
    }
}
