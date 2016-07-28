<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine\SchemaConfigurator;
use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class OfferLabelRelation implements \JsonSerializable
{
    const UUID = 'uuid';
    const RELATION_TYPE = 'relationType';
    const RELATION_ID = 'relationId';

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var OfferType
     */
    private $relationType;

    /**
     * @var StringLiteral
     */
    private $relationId;

    /**
     * Entity constructor.
     * @param UUID $uuid
     * @param OfferType $offerType
     * @param StringLiteral $relationId
     */
    public function __construct(
        UUID $uuid,
        OfferType $offerType,
        StringLiteral $relationId
    ) {
        $this->uuid = $uuid;
        $this->relationType = $offerType;
        $this->relationId = $relationId;
    }

    /**
     * @return UUID
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return OfferType
     */
    public function getRelationType()
    {
        return $this->relationType;
    }

    /**
     * @return StringLiteral
     */
    public function getRelationId()
    {
        return $this->relationId;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            self::UUID => $this->uuid->toNative(),
            self::RELATION_TYPE => $this->relationType->toNative(),
            self::RELATION_ID => $this->relationId->toNative()
        ];
    }

    /**
     * @param array $relation
     * @return OfferLabelRelation
     */
    public static function fromRelationalData(array $relation)
    {
        return new static(
            new UUID($relation[SchemaConfigurator::UUID_COLUMN]),
            OfferType::fromCaseInsensitiveValue($relation[SchemaConfigurator::OFFER_TYPE_COLUMN]),
            new StringLiteral($relation[SchemaConfigurator::OFFER_ID_COLUMN])
        );
    }
}
