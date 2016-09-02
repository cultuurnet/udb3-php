<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\UsedLabelsMemory\UsedLabelsMemoryServiceInterface;

class OfferEditingServiceWithLabelMemoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \CultureFeed_User
     */
    private $user;

    /**
     * @var OfferEditingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratee;

    /**
     * @var UsedLabelsMemoryServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelMemoryService;

    /**
     * @var OfferEditingServiceWithLabelMemory
     */
    private $editingService;

    public function setUp()
    {
        $this->user = new \CultureFeed_User();
        $this->user->id = 1;

        $this->decoratee = $this->getMock(OfferEditingServiceInterface::class);
        $this->labelMemoryService = $this->getMock(UsedLabelsMemoryServiceInterface::class);

        $this->editingService = new OfferEditingServiceWithLabelMemory(
            $this->decoratee,
            $this->user,
            $this->labelMemoryService
        );
    }

    /**
     * @test
     */
    public function it_remembers_a_used_label()
    {
        $offerId = 'offer-id-1';
        $label = new Label('foo');

        $this->decoratee->expects($this->once())
            ->method('addLabel')
            ->with($offerId, $label);

        $this->labelMemoryService->expects($this->once())
            ->method('rememberLabelUsed')
            ->with($this->user->id, $label);

        $this->editingService->addLabel($offerId, $label);
    }
}
