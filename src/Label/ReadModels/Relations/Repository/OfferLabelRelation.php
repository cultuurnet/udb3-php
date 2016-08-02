<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine\SchemaConfigurator;
use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class OfferLabelRelation implements \JsonSerializable
{
    const UUID = 'uuid';
    const OFFER_TYPE = 'offerType';
    const OFFER_ID = 'offerId';

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var OfferType
     */
    private $offerType;

    /**
     * @var StringLiteral
     */
    private $offerId;

    /**
     * Entity constructor.
     * @param UUID $uuid
     * @param OfferType $offerType
     * @param StringLiteral $offerId
     */
    public function __construct(
        UUID $uuid,
        OfferType $offerType,
        StringLiteral $offerId
    ) {
        $this->uuid = $uuid;
        $this->offerType = $offerType;
        $this->offerId = $offerId;
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
    public function getOfferType()
    {
        return $this->offerType;
    }

    /**
     * @return StringLiteral
     */
    public function getOfferId()
    {
        return $this->offerId;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            self::UUID => $this->uuid->toNative(),
            self::OFFER_TYPE => $this->offerType->toNative(),
            self::OFFER_ID => $this->offerId->toNative()
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
