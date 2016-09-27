<?php

namespace CultuurNet\UDB3\Organizer\Events;

use ValueObjects\Identity\UUID;

abstract class AbstractLabelEvent extends OrganizerEvent
{
    const LABEL_ID = 'labelId';

    /**
     * @var UUID
     */
    private $labelId;

    /**
     * @param string $organizerId
     * @param UUID $labelId
     */
    public function __construct(
        $organizerId,
        UUID $labelId
    ) {
        parent::__construct($organizerId);
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
            $data['organizer_id'],
            new UUID($data[self::LABEL_ID])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + [
            self::LABEL_ID => $this->getLabelId()->toNative()
        ];
    }
}
