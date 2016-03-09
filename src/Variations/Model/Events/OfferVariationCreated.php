<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model\Events;

use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

class OfferVariationCreated extends OfferVariationEvent
{
    /**
     * @var OwnerId
     */
    private $ownerId;

    /**
     * @var Purpose
     */
    private $purpose;

    /**
     * @var Description
     */
    private $description;

    /**
     * @var Url
     */
    private $eventUrl;

    /**
     * @var OfferType
     */
    private $offerType;

    /**
     * @param Id $id
     * @param Url $eventUrl
     * @param OwnerId $ownerId
     * @param Purpose $purpose
     * @param Description $description
     * @param OfferType $offerType
     */
    public function __construct(
        Id $id,
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description,
        OfferType $offerType
    ) {
        parent::__construct($id);

        $this->eventUrl = $eventUrl;
        $this->ownerId = $ownerId;
        $this->purpose = $purpose;
        $this->description = $description;
        $this->offerType = $offerType;
    }

    /**
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return Url
     */
    public function getEventUrl()
    {
        return $this->eventUrl;
    }

    /**
     * @return OwnerId
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @return Purpose
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * @return OfferType
     */
    public function getOfferType()
    {
        return $this->offerType;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'event_url' => (string) $this->getEventUrl(),
            'owner_id' => (string) $this->getOwnerId(),
            'purpose' => (string) $this->getPurpose(),
            'description' => (string) $this->getDescription(),
            'offer_type' => (string) $this->getOfferType()
        );
    }

    /**
     * @inheritdoc
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            new Id($data['id']),
            new Url($data['event_url']),
            new OwnerId($data['owner_id']),
            new Purpose($data['purpose']),
            new Description($data['description']),
            OfferType::fromNative($data['offer_type'])
        );
    }
}
