<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON;

use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\ReadModel\JsonDocument;

class RelatedDocument
{
    /**
     * @var OfferLabelRelation
     */
    private $offerLabelRelation;

    /**
     * @var JsonDocument
     */
    private $jsonDocument;

    /**
     * RelatedDocument constructor.
     * @param OfferLabelRelation $offerLabelRelation
     * @param JsonDocument $jsonDocument
     */
    public function __construct(OfferLabelRelation $offerLabelRelation, JsonDocument $jsonDocument)
    {
        $this->offerLabelRelation = $offerLabelRelation;
        $this->jsonDocument = $jsonDocument;
    }

    /**
     * @return JsonDocument
     */
    public function getJsonDocument()
    {
        return $this->jsonDocument;
    }

    /**
     * @return string
     */
    public function getLabelName()
    {
        return (string) $this->offerLabelRelation->getLabelName();
    }
}
