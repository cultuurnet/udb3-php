<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Organizer\Organizer;

class AbstractOrganizerCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractOrganizerCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $organizerCommand;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var string
     */
    protected $organizerId;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->organizerId = 'organizer-456';

        $this->organizerCommand = $this->getMockForAbstractClass(
            AbstractOrganizerCommand::class,
            array($this->itemId, $this->organizerId)
        );
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $organizerId = $this->organizerCommand->getOrganizerId();
        $expectedOrganizerId = 'organizer-456';

        $this->assertEquals($expectedOrganizerId, $organizerId);

        $itemId = $this->organizerCommand->getItemId();
        $expectedItemId = 'Foo';

        $this->assertEquals($expectedItemId, $itemId);
    }
}
