<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model\Events;

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
    private $originUrl;

    /**
     * @param Id $id
     * @param Url $originUrl
     * @param OwnerId $ownerId
     * @param Purpose $purpose
     * @param Description $description
     */
    public function __construct(
        Id $id,
        Url $originUrl,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description
    ) {
        parent::__construct($id);

        $this->originUrl = $originUrl;
        $this->ownerId = $ownerId;
        $this->purpose = $purpose;
        $this->description = $description;
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
    public function getOriginUrl()
    {
        return $this->originUrl;
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
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'origin_url' => (string) $this->getOriginUrl(),
            'owner_id' => (string) $this->getOwnerId(),
            'purpose' => (string) $this->getPurpose(),
            'description' => (string) $this->getDescription(),
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
            new Url($data['origin_url']),
            new OwnerId($data['owner_id']),
            new Purpose($data['purpose']),
            new Description($data['description'])
        );
    }
}
