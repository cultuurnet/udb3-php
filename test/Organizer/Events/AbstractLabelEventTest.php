<?php

namespace CultuurNet\UDB3\Organizer\Events;

use ValueObjects\Identity\UUID;

class AbstractLabelEventTest extends \PHPUnit_Framework_TestCase
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
     * @var AbstractLabelEvent
     */
    private $abstractLabelEvent;

    protected function setUp()
    {
        $this->organizerId = 'organizerId';

        $this->labelId = new UUID();

        $this->abstractLabelEvent = $this->getMockForAbstractClass(
            AbstractLabelEvent::class,
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
            $this->abstractLabelEvent->getOrganizerId()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_label_id()
    {
        $this->assertEquals(
            $this->labelId,
            $this->abstractLabelEvent->getLabelId()
        );
    }

    /**
     * @test
     */
    public function it_can_serialize()
    {
        $expectedArray = [
            'organizer_id' => $this->organizerId,
            'labelId' => $this->labelId->toNative()
        ];

        $this->assertEquals(
            $expectedArray,
            $this->abstractLabelEvent->serialize()
        );
    }
}
