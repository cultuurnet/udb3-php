<?php

namespace CultuurNet\UDB3\Label\Events;

use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;

class Created extends AbstractEvent
{
    const VISIBILITY = 'visibility';
    const PRIVACY = 'privacy';

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var Privacy
     */
    private $privacy;

    /**
     * Created constructor.
     * @param UUID $uuid
     * @param LabelName $name
     * @param Visibility $visibility
     * @param Privacy $privacy
     */
    public function __construct(
        UUID $uuid,
        LabelName $name,
        Visibility $visibility,
        Privacy $privacy
    ) {
        parent::__construct($uuid, $name);

        $this->visibility = $visibility;
        $this->privacy = $privacy;
    }

    /**
     * @return Visibility
     */
    public function getVisibility(): Visibility
    {
        return $this->visibility;
    }

    /**
     * @return Privacy
     */
    public function getPrivacy(): Privacy
    {
        return $this->privacy;
    }

    /**
     * @param array $data
     * @return Created
     */
    public static function deserialize(array $data)
    {
        return new self(
            new UUID($data[self::UUID]),
            new LabelName($data[self::NAME]),
            Visibility::fromNative($data[self::VISIBILITY]),
            Privacy::fromNative($data[self::PRIVACY])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize(): array
    {
        return parent::serialize() + [
            self::VISIBILITY => $this->getVisibility()->toNative(),
            self::PRIVACY => $this->getPrivacy()->toNative(),
        ];
    }
}
