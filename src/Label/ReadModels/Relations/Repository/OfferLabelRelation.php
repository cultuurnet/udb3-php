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
    const LABEL_NAME = 'labelName';

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
     * @var StringLiteral
     */
    private $labelName;

    /**
     * Entity constructor.
     * @param UUID $uuid
     * @param OfferType $offerType
     * @param StringLiteral $relationId
     * @param StringLiteral $labelName
     */
    public function __construct(
        UUID $uuid,
        StringLiteral $labelName,
        OfferType $offerType,
        StringLiteral $relationId
    ) {
        $this->uuid = $uuid;
        $this->relationType = $offerType;
        $this->relationId = $relationId;
        $this->labelName = $labelName;
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
     * @return StringLiteral
     */
    public function getLabelName()
    {
        return $this->labelName;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            self::UUID => $this->uuid->toNative(),
            self::LABEL_NAME => (string) $this->getLabelName(),
            self::RELATION_TYPE => $this->relationType->toNative(),
            self::RELATION_ID => $this->relationId->toNative()
        ];
    }

    public static function fromRelationalData(array $relation)
    {
        return new static(
            new UUID($relation[SchemaConfigurator::UUID_COLUMN]),
            new StringLiteral($relation[SchemaConfigurator::LABEL_NAME_COLUMN]),
            OfferType::fromCaseInsensitiveValue($relation[SchemaConfigurator::RELATION_TYPE_COLUMN]),
            new StringLiteral($relation[SchemaConfigurator::RELATION_ID_COLUMN])
        );
    }
}
