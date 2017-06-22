<?php

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use CultuurNet\UDB3\ReadModel\ConfigurableJsonDocumentLanguageAnalyzer;
use CultuurNet\UDB3\ReadModel\JsonDocument;

class PlaceJsonDocumentLanguageAnalyzer extends ConfigurableJsonDocumentLanguageAnalyzer
{
    public function __construct()
    {
        parent::__construct(
            [
                'name',
                'description',
                'address',
            ]
        );
    }

    /**
     * @todo Remove when full replay is done.
     * @replay_i18n
     * @see https://jira.uitdatabank.be/browse/III-2201
     *
     * @param JsonDocument $jsonDocument
     * @return \CultuurNet\UDB3\Language[]
     */
    public function getAllLanguages(JsonDocument $jsonDocument)
    {
        $jsonDocument = $this->polyFillMultilingualFields($jsonDocument);
        return parent::getAllLanguages($jsonDocument);
    }

    /**
     * @todo Remove when full replay is done.
     * @replay_i18n
     * @see https://jira.uitdatabank.be/browse/III-2201
     *
     * @param JsonDocument $jsonDocument
     * @return \CultuurNet\UDB3\Language[]
     */
    public function getCompletedLanguages(JsonDocument $jsonDocument)
    {
        $jsonDocument = $this->polyFillMultilingualFields($jsonDocument);
        return parent::getCompletedLanguages($jsonDocument);
    }

    /**
     * @todo Remove when full replay is done.
     * @replay_i18n
     * @see https://jira.uitdatabank.be/browse/III-2201
     *
     * @param JsonDocument $jsonDocument
     * @return JsonDocument
     */
    private function polyFillMultilingualFields(JsonDocument $jsonDocument)
    {
        $body = $jsonDocument->getBody();

        if (isset($body->address->streetAddress)) {
            $body->address = (object) [
                'nl' => $body->address,
            ];
        }

        return $jsonDocument->withBody($body);
    }
}
