<?php

namespace CultuurNet\UDB3\Label\Events;

use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;

class CopyCreated extends Created
{
    const PARENT_UUID = 'parentUuid';

    /**
     * @var UUID
     */
    private $parentUuid;

    /**
     * CopyCreated constructor.
     * @param UUID $uuid
     * @param LabelName $name
     * @param Visibility $visibility
     * @param Privacy $privacy
     * @param UUID $parentUuid
     */
    public function __construct(
        UUID $uuid,
        LabelName $name,
        Visibility $visibility,
        Privacy $privacy,
        UUID $parentUuid
    ) {
        parent::__construct($uuid, $name, $visibility, $privacy);

        $this->parentUuid = $parentUuid;
    }

    /**
     * @return UUID
     */
    public function getParentUuid()
    {
        return $this->parentUuid;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            new UUID($data[self::UUID]),
            new LabelName($data[self::NAME]),
            Visibility::fromNative($data[self::VISIBILITY]),
            Privacy::fromNative($data[self::PRIVACY]),
            new UUID($data[self::PARENT_UUID])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + [
            self::PARENT_UUID => $this->getParentUuid()->toNative(),
        ];
    }
}
