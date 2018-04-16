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
                'bookingInfo.urlLabel',
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
    public function determineAvailableLanguages(JsonDocument $jsonDocument)
    {
        $jsonDocument = $this->polyFillMultilingualFields($jsonDocument);
        return parent::determineAvailableLanguages($jsonDocument);
    }

    /**
     * @todo Remove when full replay is done.
     * @replay_i18n
     * @see https://jira.uitdatabank.be/browse/III-2201
     *
     * @param JsonDocument $jsonDocument
     * @return \CultuurNet\UDB3\Language[]
     */
    public function determineCompletedLanguages(JsonDocument $jsonDocument)
    {
        $jsonDocument = $this->polyFillMultilingualFields($jsonDocument);
        return parent::determineCompletedLanguages($jsonDocument);
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
        $mainLanguage = isset($body->mainLanguage) ? $body->mainLanguage : 'nl';

        if (isset($body->address->streetAddress)) {
            $body->address = (object) [
                $mainLanguage => $body->address,
            ];
        }

        if (isset($body->bookingInfo->urlLabel) && is_string($body->bookingInfo->urlLabel)) {
            $body->bookingInfo->urlLabel = (object) [
                $mainLanguage => $body->bookingInfo->urlLabel,
            ];
        }

        return $jsonDocument->withBody($body);
    }
}
