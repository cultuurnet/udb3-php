<?php

namespace CultuurNet\UDB3\Event\Commands;

class DeleteOrganizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeleteOrganizer
     */
    protected $deleteOrganizer;

    public function setUp()
    {
        $this->deleteOrganizer = new DeleteOrganizer('id', 'organizerId');
    }

    /**
     * @test
     */
    public function it_is_possible_to_instantiate_the_command_with_parameters()
    {
        $expectedDeleteOrganizer = new DeleteOrganizer('id', 'organizerId');

        $this->assertEquals($expectedDeleteOrganizer, $this->deleteOrganizer);
    }
}
