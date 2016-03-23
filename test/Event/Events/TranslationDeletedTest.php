<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class TranslationDeletedTest extends \PHPUnit_Framework_TestCase
{
    public function serializationDataProvider()
    {
        return [
            'english' => [
                [
                    'event_id' => 'foo',
                    'language' => 'en'
                ],
                new TranslationDeleted(
                    new String('foo'),
                    new Language('en')
                ),
            ],
            'french' => [
                [
                    'event_id' => 'bar',
                    'language' => 'fr'
                ],
                new TranslationDeleted(
                    new String('bar'),
                    new Language('fr')
                ),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param TranslationDeleted $translationDeleted
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        TranslationDeleted $translationDeleted
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $translationDeleted->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $serializedValue
     * @param TranslationDeleted $expectedTranslationDeleted
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        TranslationDeleted $expectedTranslationDeleted
    ) {
        $this->assertEquals(
            $expectedTranslationDeleted,
            TranslationDeleted::deserialize($serializedValue)
        );
    }
}
