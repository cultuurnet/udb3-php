<?php

namespace CultuurNet\UDB3\Role\Events;

use ValueObjects\Identity\UUID;

class AbstractLabelEvent extends AbstractEvent
{
    const LABEL_ID = 'labelId';

    /**
     * @var UUID
     */
    private $labelId;

    /**
     * @param UUID $uuid
     * @param UUID $labelId
     */
    public function __construct(
        UUID $uuid,
        UUID $labelId
    ) {
        parent::__construct($uuid);
        $this->labelId = $labelId;
    }

    /**
     * @return UUID
     */
    public function getLabelId()
    {
        return $this->labelId;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            new UUID($data[self::UUID]),
            new UUID($data[self::LABEL_ID])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + [self::LABEL_ID => $this->getLabelId()->toNative()];
    }
}
