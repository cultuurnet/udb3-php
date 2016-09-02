<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\UsedLabelsMemory\UsedLabelsMemoryServiceInterface;
use ValueObjects\String\String as StringLiteral;

class OfferEditingServiceWithLabelMemory implements OfferEditingServiceInterface
{
    use OfferEditingServiceDecoratorTrait {
        OfferEditingServiceDecoratorTrait::addLabel as addLabelViaDecoratee;
    }

    /**
     * @var OfferEditingServiceInterface
     */
    private $editingService;

    /**
     * @var \CultureFeed_User
     */
    private $user;

    /**
     * @var UsedLabelsMemoryServiceInterface
     */
    private $labelMemoryService;

    /**
     * @param OfferEditingServiceInterface $editingService
     * @param \CultureFeed_User $user
     * @param UsedLabelsMemoryServiceInterface $labelMemoryService
     */
    public function __construct(
        OfferEditingServiceInterface $editingService,
        \CultureFeed_User $user,
        UsedLabelsMemoryServiceInterface $labelMemoryService
    ) {
        $this->editingService = $editingService;
        $this->user = $user;
        $this->labelMemoryService = $labelMemoryService;
    }

    /**
     * @return OfferEditingServiceInterface
     */
    protected function getDecoratedEditingService()
    {
        return $this->editingService;
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel($id, Label $label)
    {
        $commandId = $this->addLabelViaDecoratee($id, $label);

        $this->labelMemoryService->rememberLabelUsed(
            $this->user->id,
            $label
        );

        return $commandId;
    }
}
