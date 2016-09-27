<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use ValueObjects\Identity\UUID;

class AbstractLabelCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $organizerId;

    /**
     * @var UUID
     */
    private $labelId;

    /**
     * @var AbstractLabelCommand
     */
    private $abstractLabelCommand;

    protected function setUp()
    {
        $this->organizerId = 'organizerId';

        $this->labelId = new UUID();

        $this->abstractLabelCommand = $this->getMockForAbstractClass(
            AbstractLabelCommand::class,
            [$this->organizerId, $this->labelId]
        );
    }

    /**
     * @test
     */
    public function it_stores_an_organizer_id()
    {
        $this->assertEquals(
            $this->organizerId,
            $this->abstractLabelCommand->getOrganizerId()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_label_id()
    {
        $this->assertEquals(
            $this->labelId,
            $this->abstractLabelCommand->getLabelId()
        );
    }
}
