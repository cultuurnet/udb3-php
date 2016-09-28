<?php

namespace CultuurNet\UDB3\Organizer\Events;

use ValueObjects\Identity\UUID;

class LabelAddedTest extends \PHPUnit_Framework_TestCase
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
     * @var LabelAdded
     */
    private $labelAdded;

    protected function setUp()
    {
        $this->organizerId = 'organizerId';

        $this->labelId = new UUID();

        $this->labelAdded = new LabelAdded(
            $this->organizerId,
            $this->labelId
        );
    }

    /**
     * @test
     */
    public function it_derives_from_abstract_label_event()
    {
        $this->assertInstanceOf(AbstractLabelEvent::class, $this->labelAdded);
    }

    /**
     * @test
     */
    public function it_can_deserialize()
    {
        $labelAddedAsArray = [
            'organizer_id' => $this->labelAdded->getOrganizerId(),
            'labelId' => $this->labelAdded->getLabelId()
        ];

        $labelAdded = LabelAdded::deserialize($labelAddedAsArray);

        $this->assertEquals($this->labelAdded, $labelAdded);
    }
}
