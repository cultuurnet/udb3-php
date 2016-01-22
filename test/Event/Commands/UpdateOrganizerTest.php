<?php

namespace CultuurNet\UDB3\Event\Commands;

class UpdateOrganizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateOrganizer
     */
    protected $updateOrganizer;

    public function setUp()
    {
        $this->updateOrganizer = new UpdateOrganizer('id', 'organizerId');
    }

    /**
     * @test
     */
    public function it_is_possible_to_instantiate_the_command_with_parameters()
    {
        $expectedUpdateOrganizer = new UpdateOrganizer('id', 'organizerId');

        $this->assertEquals($expectedUpdateOrganizer, $this->updateOrganizer);
    }
}
