<?php

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\ReadModel\JsonDocument;

class PlaceJsonDocumentLanguageAnalyzerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PlaceJsonDocumentLanguageAnalyzer
     */
    private $analyzer;

    public function setUp()
    {
        $this->analyzer = new PlaceJsonDocumentLanguageAnalyzer();
    }

    /**
     * @test
     */
    public function it_should_return_a_list_of_all_languages_found_on_multilingual_fields()
    {
        $data = [
            '@id' => 'https://io.uitdatabank.be/organizers/919c7904-ecfa-440c-92d0-ae912213c615',
            'name' => [
                'nl' => 'Naam NL',
                'fr' => 'Nom FR',
                'en' => 'Name EN',
                'de' => 'Name DE',
            ],
            'description' => [
                'nl' => 'Teaser NL',
                'fr' => 'Teaser FR',
                'de' => 'Teaser DE',
            ],
            'address' => [
                'nl' => [
                    'streetAddress' => 'Wetstraat 1',
                    'postalCode' => '1000',
                    'addressLocality' => 'Brussel',
                    'addressCountry' => 'BE',
                ],
                'fr' => [
                    'streetAddress' => 'Rue de la loi 1',
                    'postalCode' => '1000',
                    'addressLocality' => 'Bruxelles',
                    'addressCountry' => 'BE',
                ],
            ],
        ];

        $document = new JsonDocument('919c7904-ecfa-440c-92d0-ae912213c615', json_encode($data));

        $expected = [
            new Language('nl'),
            new Language('fr'),
            new Language('en'),
            new Language('de'),
        ];

        $actual = $this->analyzer->getAllLanguages($document);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_should_return_a_list_of_languages_found_on_every_one_multilingual_field()
    {
        $data = [
            '@id' => 'https://io.uitdatabank.be/organizers/919c7904-ecfa-440c-92d0-ae912213c615',
            'name' => [
                'nl' => 'Naam NL',
                'fr' => 'Nom FR',
                'en' => 'Name EN',
                'de' => 'Name DE',
            ],
            'description' => [
                'nl' => 'Teaser NL',
                'fr' => 'Teaser FR',
                'de' => 'Teaser DE',
            ],
            'address' => [
                'nl' => [
                    'streetAddress' => 'Wetstraat 1',
                    'postalCode' => '1000',
                    'addressLocality' => 'Brussel',
                    'addressCountry' => 'BE',
                ],
                'fr' => [
                    'streetAddress' => 'Rue de la loi 1',
                    'postalCode' => '1000',
                    'addressLocality' => 'Bruxelles',
                    'addressCountry' => 'BE',
                ],
            ],
        ];

        $document = new JsonDocument('919c7904-ecfa-440c-92d0-ae912213c615', json_encode($data));

        $expected = [
            new Language('nl'),
            new Language('fr'),
        ];

        $actual = $this->analyzer->getCompletedLanguages($document);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_should_polyfill_address_projections_from_a_single_object_to_multilingual_projections()
    {
        $data = [
            '@id' => 'https://io.uitdatabank.be/organizers/919c7904-ecfa-440c-92d0-ae912213c615',
            'name' => [
                'nl' => 'Naam NL',
                'fr' => 'Nom FR',
                'en' => 'Name EN',
                'de' => 'Name DE',
            ],
            'description' => [
                'nl' => 'Teaser NL',
                'fr' => 'Teaser FR',
                'de' => 'Teaser DE',
            ],
            'address' => [
                'streetAddress' => 'Wetstraat 1',
                'postalCode' => '1000',
                'addressLocality' => 'Brussel',
                'addressCountry' => 'BE',
            ],
        ];

        $document = new JsonDocument('919c7904-ecfa-440c-92d0-ae912213c615', json_encode($data));

        $expectedAll = [
            new Language('nl'),
            new Language('fr'),
            new Language('en'),
            new Language('de'),
        ];

        $expectedCompleted = [
            new Language('nl'),
        ];

        $actualAll = $this->analyzer->getAllLanguages($document);
        $actualCompleted = $this->analyzer->getCompletedLanguages($document);

        $this->assertEquals($expectedAll, $actualAll);
        $this->assertEquals($expectedCompleted, $actualCompleted);
    }
}
