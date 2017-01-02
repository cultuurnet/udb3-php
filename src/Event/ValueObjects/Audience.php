<?php

namespace CultuurNet\UDB3\Event\ValueObjects;

use Broadway\Serializer\SerializableInterface;

class Audience implements SerializableInterface
{
    /**
     * @var AudienceType
     */
    private $audienceType;

    /**
     * Audience constructor.
     * @param AudienceType $audienceType
     */
    public function __construct(AudienceType $audienceType)
    {
        $this->audienceType = $audienceType;
    }

    /**
     * @return AudienceType
     */
    public function getAudienceType()
    {
        return $this->audienceType;
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
            'audienceType' => $this->audienceType->toNative()
        ];
    }

    /**
     * @param Audience $otherAudience
     * @return bool
     */
    public function equals(Audience $otherAudience)
    {
        return $this->audienceType === $otherAudience->getAudienceType();
    }
}
