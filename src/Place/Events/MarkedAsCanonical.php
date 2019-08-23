<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Place\PlaceEvent;

final class MarkedAsCanonical extends PlaceEvent
{
    /**
     * @var string
     */
    private $duplicatedBy;

    public function __construct(string $placeId, string $duplicatedBy)
    {
        parent::__construct($placeId);
        $this->duplicatedBy = $duplicatedBy;
    }

    public function getDuplicatedBy(): string
    {
        return $this->duplicatedBy;
    }

    public function serialize()
    {
        return parent::serialize() + [
                'duplicated_by' => $this->duplicatedBy,
            ];
    }

    public static function deserialize(array $data)
    {
        return new static($data['place_id'], ($data['duplicated_by']));
    }
}
