<?php

namespace CultuurNet\UDB3\Offer\Events\Moderation;

use CultuurNet\UDB3\Offer\Events\AbstractEvent;

abstract class AbstractPublished extends AbstractEvent
{
    /** @var  \DateTimeInterface */
    private $embargoDate;

    /**
     * AbstractPublish constructor.
     * @param string $itemId
     * @param \DateTimeInterface|null $embargoDate
     */
    public function __construct($itemId, \DateTimeInterface $embargoDate = null)
    {
        parent::__construct($itemId);

        $this->embargoDate = $embargoDate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEmbargoDate()
    {
        return $this->embargoDate;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        $asArray = parent::serialize();

        if (!is_null($this->embargoDate))
        {
            $asArray['embargo_date'] = $this->embargoDate->format(\DateTime::ATOM);
        }

        return $asArray;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], $data['embargo_date']);
    }
}
