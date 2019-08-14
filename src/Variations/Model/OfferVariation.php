<?php

namespace CultuurNet\UDB3\Variations\Model;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Variations\AggregateDeletedException;
use CultuurNet\UDB3\Variations\Deleteable;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationCreated;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationDeleted;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

class OfferVariation extends EventSourcedAggregateRoot implements Deleteable
{
    /**
     * @var Id
     */
    private $id;

    /**
     * @var Description
     */
    private $description;

    /**
     * @var Purpose
     */
    private $purpose;

    /**
     * @var Url
     */
    private $originUrl;

    /**
     * @var OwnerId
     */
    private $ownerId;

    /**
     * @var boolean
     */
    private $deleted = false;

    /**
     * @param Id $id
     * @param Url $originUrl
     * @param Purpose $purpose
     * @param OwnerId $ownerId
     * @param Description $description
     * @return static
     */
    public static function create(
        Id $id,
        Url $originUrl,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description
    ) {
        $variation = new static();
        $variation->apply(
            new OfferVariationCreated(
                $id,
                $originUrl,
                $ownerId,
                $purpose,
                $description
            )
        );

        return $variation;
    }

    /**
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param Description $description
     *
     * @throws AggregateDeletedException
     */
    public function editDescription(Description $description)
    {
        $this->guardNotDeleted();

        if ($description !== $this->description) {
            $this->apply(new DescriptionEdited($this->id, $description));
        }
    }

    protected function applyDescriptionEdited(DescriptionEdited $descriptionEdited)
    {
        $this->description = $descriptionEdited->getDescription();
    }

    protected function applyOfferVariationCreated(OfferVariationCreated $offerVariationCreated)
    {
        $this->id = $offerVariationCreated->getId();
        $this->purpose = $offerVariationCreated->getPurpose();
        $this->description = $offerVariationCreated->getDescription();
        $this->ownerId = $offerVariationCreated->getOwnerId();
        $this->originUrl = $offerVariationCreated->getOriginUrl();
    }

    protected function applyOfferVariationDeleted()
    {
        $this->deleted = true;
    }

    private function guardNotDeleted()
    {
        if ($this->isDeleted()) {
            throw new AggregateDeletedException((string) $this->id);
        }
    }

    /**
     * @inheritdoc
     */
    public function markDeleted()
    {
        $this->guardNotDeleted();
        $this->apply(new OfferVariationDeleted($this->id));
    }

    /**
     * @inheritdoc
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return (string) $this->id;
    }

    /**
     * @return Purpose
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * @return OwnerId
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @return Url
     */
    public function getOriginUrl()
    {
        return $this->originUrl;
    }
}
