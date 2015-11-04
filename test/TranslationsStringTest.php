<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 04/11/15
 * Time: 16:15
 */

namespace CultuurNet\UDB3;

class TranslationsStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_validates_for_a_lang_key()
    {
        $this->setExpectedException(\CultuurNet\UDB3\KeyNotFoundException::class);
        $translationsString = new TranslationsString(
            file_get_contents(__DIR__.'/samples/translations_without_lang_key.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_all_possible_properties()
    {
        $translationsString = new TranslationsString(
            file_get_contents(__DIR__.'/samples/translations_with_all_possible_properties.txt')
        );

        $expectedLang = 'FR';
        $expectedTitle = 'Dizorkestra en concert';
        $expectedShortdescription = 'Concert Dizôrkestra, un groupe qui.';
        $expectedLongdescription = 'Concert Dizôrkestra, un groupe qui se montre inventif.';
        $expectedParsedData = [
            'lang' => $expectedLang,
            'title' => $expectedTitle,
            'shortdescription' => $expectedShortdescription,
            'longdescription' => $expectedLongdescription
        ];

        $this->assertEquals($expectedLang, $translationsString->getLang());
        $this->assertEquals($expectedTitle, $translationsString->getTitle());
        $this->assertEquals($expectedShortdescription, $translationsString->getShortdescription());
        $this->assertEquals($expectedLongdescription, $translationsString->getLongdescription());
        $this->assertEquals($expectedParsedData, $translationsString->getParsedData());
    }
}
