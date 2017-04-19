<?php

namespace CultuurNet\UDB3\ValueObject;

use CultuurNet\UDB3\Language;
use ValueObjects\StringLiteral\StringLiteral;

class MultilingualStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Language
     */
    private $originalLanguage;

    /**
     * @var StringLiteral
     */
    private $originalString;

    /**
     * @var StringLiteral[]
     */
    private $translations;

    /**
     * @var MultilingualString
     */
    private $multilingualString;

    public function setUp()
    {
        $this->originalLanguage = new Language('nl');
        $this->originalString = new StringLiteral(
            'Hebban olla uogala nestas hagunnan hinase hic anda thu uuat unbidan uue nu'
        );

        $this->translations = [
            'fr' => new StringLiteral('Tous les oiseaux ont commencé nids, sauf moi et vous. Ce que nous attendons?'),
            'en' => new StringLiteral('All birds have begun nests, except me and you. What we are waiting for?'),
        ];

        $this->multilingualString = (new MultilingualString($this->originalLanguage, $this->originalString))
            ->withTranslation(new Language('fr'), $this->translations['fr'])
            ->withTranslation(new Language('en'), $this->translations['en']);
    }

    /**
     * @test
     */
    public function it_returns_the_original_language_and_string()
    {
        $this->assertEquals($this->originalLanguage, $this->multilingualString->getOriginalLanguage());
        $this->assertEquals($this->originalString, $this->multilingualString->getOriginalString());
    }

    /**
     * @test
     */
    public function it_returns_all_translations()
    {
        $this->assertEquals($this->translations, $this->multilingualString->getTranslations());
    }

    /**
     * @test
     */
    public function it_returns_all_translations_including_the_original_language__string()
    {
        $expected = [
            'nl' => new StringLiteral(
                'Hebban olla uogala nestas hagunnan hinase hic anda thu uuat unbidan uue nu'
            ),
            'fr' => new StringLiteral(
                'Tous les oiseaux ont commencé nids, sauf moi et vous. Ce que nous attendons?'
            ),
            'en' => new StringLiteral(
                'All birds have begun nests, except me and you. What we are waiting for?'
            ),
        ];

        $this->assertEquals($expected, $this->multilingualString->getTranslationsIncludingOriginal());
    }

    /**
     * @test
     */
    public function it_does_not_allow_translations_of_the_original_language()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Can not translate to original language.');

        $this->multilingualString->withTranslation(
            new Language('nl'),
            new StringLiteral('Alle vogels zijn nesten begonnen, behalve ik en jij. Waar wachten wij nu op?')
        );
    }

    /**
     * @test
     * @dataProvider stringForLanguageDataProvider
     *
     * @param Language $preferredLanguage
     * @param Language[] $fallbackLanguages
     * @param StringLiteral|null $expected
     */
    public function it_can_return_the_value_for_a_given_language_or_a_fallback_language(
        Language $preferredLanguage,
        array $fallbackLanguages,
        StringLiteral $expected = null
    ) {
        $actual = $this->multilingualString->getStringForLanguage($preferredLanguage, ...$fallbackLanguages);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function stringForLanguageDataProvider()
    {
        return [
            [
                new Language('nl'),
                [new Language('fr'), new Language('en')],
                new StringLiteral('Hebban olla uogala nestas hagunnan hinase hic anda thu uuat unbidan uue nu'),
            ],
            [
                new Language('de'),
                [new Language('fr'), new Language('en')],
                new StringLiteral('Tous les oiseaux ont commencé nids, sauf moi et vous. Ce que nous attendons?'),
            ],
            [
                new Language('de'),
                [new Language('es'), new Language('en')],
                new StringLiteral('All birds have begun nests, except me and you. What we are waiting for?'),
            ],
            [
                new Language('de'),
                [new Language('es'), new Language('ch')],
                null,
            ],
        ];
    }
}
