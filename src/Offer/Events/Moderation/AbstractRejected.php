<?php

namespace CultuurNet\UDB3\Offer\Events\Moderation;

use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use ValueObjects\StringLiteral\StringLiteral;

abstract class AbstractRejected extends AbstractEvent
{
    /**
     * @var StringLiteral
     *  The reason why an offer is rejected, e.g.: Image and price info is missing.
     */
    private $reason;

    /**
     * {@inheritdoc}
     *
     * @param StringLiteral $reason
     *  The reason why an offer is rejected, e.g.: Image and price info is missing.
     */
    final public function __construct(string $itemId, StringLiteral $reason)
    {
        parent::__construct($itemId);
        $this->reason = $reason;
    }

    /**
     * @return StringLiteral
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'reason' => $this->reason->toNative(),
        );
    }

    /**
     * @param array $data
     * @return AbstractRejected
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            new StringLiteral($data['reason'])
        );
    }
}
