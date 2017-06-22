<?php

namespace CultuurNet\UDB3\ReadModel;

use CultuurNet\UDB3\Language;

class ConfigurableJsonDocumentLanguageAnalyzer implements JsonDocumentLanguageAnalyzerInterface
{
    /**
     * @var string[]
     */
    private $translatableProperties;

    /**
     * @param string[] $translatableProperties
     *   List of translatable properties (on top level).
     */
    public function __construct(
        array $translatableProperties
    ) {
        $this->translatableProperties = $translatableProperties;
    }

    /**
     * @param JsonDocument $jsonDocument
     * @return Language[]
     */
    public function getAllLanguages(JsonDocument $jsonDocument)
    {
        $json = $jsonDocument->getBody();
        $languageStrings = [];

        foreach ($this->translatableProperties as $translatableProperty) {
            $languageStringsOnProperty = $this->getLanguageStringsFromProperty($json, $translatableProperty);

            $languageStrings = array_merge(
                $languageStrings,
                $languageStringsOnProperty
            );
        }

        $languageStrings = array_values(array_unique($languageStrings));

        return $this->getLanguageStringsAsValueObjects($languageStrings);
    }

    /**
     * @param JsonDocument $jsonDocument
     * @return Language[]
     */
    public function getCompletedLanguages(JsonDocument $jsonDocument)
    {
        $json = $jsonDocument->getBody();
        $languageStrings = [];

        foreach ($this->translatableProperties as $translatableProperty) {
            $languageStringsOnProperty = $this->getLanguageStringsFromProperty($json, $translatableProperty);

            if ($translatableProperty == $this->translatableProperties[0]) {
                $languageStrings = $languageStringsOnProperty;
            } else {
                $languageStrings = array_intersect($languageStrings, $languageStringsOnProperty);
            }
        }

        $languageStrings = array_values(array_unique($languageStrings));

        return $this->getLanguageStringsAsValueObjects($languageStrings);
    }

    /**
     * @param \stdClass $json
     * @param string $propertyName
     * @return string[]
     */
    private function getLanguageStringsFromProperty(\stdClass $json, $propertyName)
    {
        if (!isset($json->{$propertyName})) {
            return [];
        }

        return array_keys(
            get_object_vars($json->{$propertyName})
        );
    }

    /**
     * @param string[] $languageStrings
     * @return Language[]
     */
    private function getLanguageStringsAsValueObjects(array $languageStrings)
    {
        return array_map(
            function($languageString) {
                return new Language($languageString);
            },
            $languageStrings
        );
    }
}
