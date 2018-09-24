<?php

namespace CultuurNet\UDB3\Event\ValueObjects;

use Broadway\Serializer\SerializableInterface;

class Audience implements SerializableInterface
{
    /**
     * Store the Audience enum internally as a string to make sure that PHP encode works.
     * @var string
     */
    private $audienceType;

    /**
     * Audience constructor.
     * @param AudienceType $audienceType
     */
    public function __construct(AudienceType $audienceType)
    {
        $this->audienceType = $audienceType->toNative();
    }

    /**
     * @return AudienceType
     */
    public function getAudienceType()
    {
        return AudienceType::fromNative($this->audienceType);
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            AudienceType::fromNative($data['audienceType'])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return [
            'audienceType' => $this->getAudienceType()->toNative(),
        ];
    }

    /**
     * @param Audience $otherAudience
     * @return bool
     */
    public function equals(Audience $otherAudience)
    {
        return $this->getAudienceType() === $otherAudience->getAudienceType();
    }
}
