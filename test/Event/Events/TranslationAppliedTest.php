<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class TranslationAppliedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_requires_one_of_title_short_or_long_description()
    {
        $this->setExpectedException(\LogicException::class);

        new TranslationApplied(
            new String('id'),
            new Language('en')
        );
    }

    public function serializationDataProvider()
    {
        return [
            'without title and short description' => [
                [
                    'event_id' => 'test 456',
                    'language' => 'en',
                    'long_description' => 'Long description of activity test 456',
                ],
                new TranslationApplied(
                    new String('test 456'),
                    new Language('en'),
                    null,
                    null,
                    new String('Long description of activity test 456')
                ),
            ],
            'without long description' => [
                [
                    'event_id' => 'test 789',
                    'language' => 'en',
                    'short_description' => 'Short description of activity test 789',
                ],
                new TranslationApplied(
                    new String('test 789'),
                    new Language('en'),
                    null,
                    new String('Short description of activity test 789'),
                    null
                ),
            ],
            'full' => [
                [
                    'event_id' => '123',
                    'language' => 'fr',
                    'title' => 'Test 123',
                    'short_description' => 'Short description of activity test 123',
                    'long_description' => 'Long description of activity test 123',
                ],
                new TranslationApplied(
                    new String('123'),
                    new Language('fr'),
                    new String('Test 123'),
                    new String('Short description of activity test 123'),
                    new String('Long description of activity test 123')
                ),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        TranslationApplied $translationApplied
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $translationApplied->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        TranslationApplied $expectedTranslationApplied
    ) {
        $this->assertEquals(
            $expectedTranslationApplied,
            TranslationApplied::deserialize($serializedValue)
        );
    }
}
