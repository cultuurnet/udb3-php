<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class TranslationTest extends \PHPUnit_Framework_TestCase
{
    public function mergeDataProvider()
    {
        $a = new Translation(
            new Language('en'),
            new String('title A'),
            new String('short description A'),
            new String('long description A')
        );

        $b = new Translation(
            new Language('en'),
            new String('title B'),
            new String('short description B'),
            new String('long description B')
        );

        $c = new Translation(
            new Language('en'),
            new String('title C'),
            null,
            null,
            null
        );

        return [
            'complete translations #1' => [
                $a,
                $b,
                $b,
            ],
            'complete translations #2' =>[
                $b,
                $a,
                $a,
            ],
            'complete translation merged into incomplete translation' => [
                $c,
                $a,
                $a,
            ],
            'incomplete translation merged into complete translation #1' => [
                $a,
                $c,
                new Translation(
                    new Language('en'),
                    new String('title C'),
                    new String('short description A'),
                    new String('long description A')
                ),
            ],
            'incomplete translation merged into complete translation #2' => [
                $b,
                $c,
                new Translation(
                    new Language('en'),
                    new String('title C'),
                    new String('short description B'),
                    new String('long description B')
                ),
            ],
            'incomplete translation merged into incomplete translation' => [
                $c,
                $c,
                $c,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider mergeDataProvider
     */
    public function it_can_be_merged_with_another_translation(
        Translation $translationA,
        Translation $translationB,
        Translation $expectedTranslation
    ) {
        $this->assertEquals(
            $expectedTranslation,
            $translationA->mergeTranslation($translationB)
        );
    }
}
