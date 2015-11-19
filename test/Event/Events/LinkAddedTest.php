<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 19/11/15
 * Time: 11:01
 */

namespace test\Event\Events;

use CultuurNet\UDB3\Event\Events\LinkAdded;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\LinkType;
use ValueObjects\String\String;

class LinkAddedTest extends \PHPUnit_Framework_TestCase
{
    public function serializationDataProvider()
    {
        return [
            'english' => [
                [
                    'event_id' => 'foo',
                    'language' => 'en',
                    'link' => 'link en',
                    'link_type' => 'roadmap',
                    'title' => 'title',
                    'copyright' => 'copyright',
                    'sub_brand' => 'sub brand',
                    'description' => 'description'
                ],
                new LinkAdded(
                    new String('foo'),
                    new Language('en'),
                    new String('link en'),
                    new LinkType('roadmap'),
                    new String('title'),
                    new String('copyright'),
                    new String('sub brand'),
                    new String('description')
                ),
            ],
            'french' => [
                [
                    'event_id' => 'bar',
                    'language' => 'fr',
                    'link' => 'link fr',
                    'link_type' => 'roadmap',
                    'title' => 'title fr',
                    'copyright' => 'copyright fr',
                    'sub_brand' => 'sub brand fr',
                    'description' => 'description fr'
                ],
                new LinkAdded(
                    new String('bar'),
                    new Language('fr'),
                    new String('link fr'),
                    new LinkType('roadmap'),
                    new String('title fr'),
                    new String('copyright fr'),
                    new String('sub brand fr'),
                    new String('description fr')
                ),
            ],
            'english optional parameters' => [
                [
                    'event_id' => 'foo',
                    'language' => 'en',
                    'link' => 'link en',
                    'link_type' => 'roadmap'
                ],
                new LinkAdded(
                    new String('foo'),
                    new Language('en'),
                    new String('link en'),
                    new LinkType('roadmap'),
                    null,
                    null,
                    null,
                    null
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
        LinkAdded $linkAdded
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $linkAdded->serialize()
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
        LinkAdded $expectedLinkAdded
    ) {
        $this->assertEquals(
            $expectedLinkAdded,
            LinkAdded::deserialize($serializedValue)
        );
    }
}
