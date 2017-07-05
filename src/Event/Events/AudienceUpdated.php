<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class AudienceUpdated extends AbstractEvent
{
    /**
     * @var Audience
     */
    private $audience;

    /**
     * AudienceUpdated constructor.
     * @param string $itemId
     * @param Audience $audience
     */
    public function __construct(
        $itemId,
        Audience $audience
    ) {
        parent::__construct($itemId);

        $this->audience = $audience;
    }

    /**
     * @return Audience
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + [
                'audience' => $this->audience->serialize(),
            ];
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            Audience::deserialize($data['audience'])
        );
    }
}
