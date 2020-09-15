<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

final class AudienceUpdated extends AbstractEvent
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
        string $itemId,
        Audience $audience
    ) {
        parent::__construct($itemId);

        $this->audience = $audience;
    }

    /**
     * @return Audience
     */
    public function getAudience(): Audience
    {
        return $this->audience;
    }

    /**
     * @inheritdoc
     */
    public function serialize(): array
    {
        return parent::serialize() + [
                'audience' => $this->audience->serialize(),
            ];
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data): AudienceUpdated
    {
        return new self(
            $data['item_id'],
            Audience::deserialize($data['audience'])
        );
    }
}
