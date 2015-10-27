<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 27/10/15
 * Time: 09:41
 */

namespace CultuurNet\UDB3;

class KeywordsStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_validates_for_an_ampersand()
    {
        $this->setExpectedException(\CultuurNet\UDB3\CharacterNotFoundException::class);
        $keywordsString = new KeywordsString(file_get_contents(__DIR__.'/samples/KeywordsStringWithoutAmpersand.txt'));
    }

    /**
     * @test
     */
    public function it_validates_for_not_more_than_one_ampersand()
    {
        $this->setExpectedException(\CultuurNet\UDB3\TooManySpecificCharactersException::class);
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithTooManyAmpersands.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_for_a_keywords_key()
    {
        $this->setExpectedException(\CultuurNet\UDB3\KeyNotFoundException::class);
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithoutKeywordsKey.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_for_a_visibles_key()
    {
        $this->setExpectedException(\CultuurNet\UDB3\KeyNotFoundException::class);
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithoutVisiblesKey.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_for_an_unexpected_key()
    {
        $this->setExpectedException(\CultuurNet\UDB3\KeyNotFoundException::class, 'Expected visibles, found user');
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithAnUnexpectedKey.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_when_using_too_much_visibles_values()
    {
        $this->setExpectedException(\CultuurNet\UDB3\UnequalAmountOfValuesException::class);
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithTooMuchVisiblesValues.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_when_using_too_much_keywords_values()
    {
        $this->setExpectedException(\CultuurNet\UDB3\UnequalAmountOfValuesException::class);
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithTooMuchKeywordsValues.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_for_a_one_element_values_array()
    {
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithOneKeywordAndOneVisible.txt')
        );

        $expectedKeywords = array('keyword1');
        $expectedVisibles = array('true');

        $this->assertEquals($expectedKeywords, $keywordsString->getKeywords());
        $this->assertEquals($expectedVisibles, $keywordsString->getVisibles());
    }
}
