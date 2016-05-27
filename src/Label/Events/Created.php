<?php

namespace CultuurNet\UDB3\Label\Events;

use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class Created extends Event
{
    const NAME = 'name';
    const VISIBILITY = 'visibility';
    const PRIVACY = 'privacy';

    /**
     * @var StringLiteral
     */
    private $name;

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
     * @param StringLiteral $name
     * @param Visibility $visibility
     * @param Privacy $privacy
     */
    public function __construct(
        UUID $uuid,
        StringLiteral $name,
        Visibility $visibility,
        Privacy $privacy
    ) {
        parent::__construct($uuid);

        $this->name = $name;
        $this->visibility = $visibility;
        $this->privacy = $privacy;
    }

    /**
     * @return StringLiteral
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Visibility
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return Privacy
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            new UUID($data[self::UUID]),
            new StringLiteral($data[self::NAME]),
            Visibility::fromNative($data[self::VISIBILITY]),
            Privacy::fromNative($data[self::PRIVACY])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + [
            self::NAME => $this->getName()->toNative(),
            self::VISIBILITY => $this->getVisibility()->toNative(),
            self::PRIVACY => $this->getPrivacy()->toNative()
        ];
    }
}
