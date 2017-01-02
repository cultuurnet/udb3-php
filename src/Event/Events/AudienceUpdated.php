<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class AudienceUpdated extends AbstractEvent
{
    /**
     * @var AudienceType
     */
    private $audienceType;

    /**
     * AudienceUpdated constructor.
     * @param string $itemId
     * @param AudienceType $audienceType
     */
    public function __construct(
        $itemId,
        AudienceType $audienceType
    ) {
        parent::__construct($itemId);

        $this->audienceType = $audienceType;
    }

    /**
     * @return AudienceType
     */
    public function getAudienceType()
    {
        return $this->audienceType;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + [
                'audience_type' => $this->audienceType->toNative()
            ];
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            AudienceType::fromNative($data['audience_type'])
        );
    }
}
