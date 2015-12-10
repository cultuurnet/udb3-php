<?php

namespace test\Event\Events;

use CultuurNet\UDB3\Event\Events\CollaborationDataAdded;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\CollaborationData\Description;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

class CollaborationDataAddedTest extends \PHPUnit_Framework_TestCase
{
    public function serializationDataProvider()
    {
        $descriptionEn = json_encode(
            [
                'keyword' => 'Lorem',
                'text' => 'description EN',
                'image' => '/image.en.png',
                'article' => 'Ipsum'
            ]
        );

        $descriptionFr = json_encode(
            [
                'keyword' => 'Lorèm',
                'text' => 'description FR',
                'image' => '/image.fr.png',
                'article' => 'Ipsume'
            ]
        );

        return [
            // English.
            [
                [
                    'event_id' => 'foo',
                    'language' => 'en',
                    'sub_brand' => 'sub brand',
                    'description' => $descriptionEn,
                    'title' => 'title',
                    'copyright' => 'copyright',
                    'link' => 'http://google.com',
                    'link_type' => 'collaboration',
                ],
                (new CollaborationDataAdded(
                    new String('foo'),
                    new Language('en'),
                    new String('sub brand'),
                    new Description($descriptionEn)
                ))
                    ->withTitle(
                        new String('title')
                    )
                    ->withCopyright(
                        new String('copyright')
                    )
                    ->withUrl(
                        Url::fromNative('http://google.com')
                    ),
            ],
            // French.
            [
                [
                    'event_id' => 'bar',
                    'language' => 'fr',
                    'sub_brand' => 'sub brand fr',
                    'description' => $descriptionFr,
                    'title' => 'title fr',
                    'copyright' => 'copyright fr',
                    'link' => 'http://google.fr',
                    'link_type' => 'collaboration',
                ],
                (new CollaborationDataAdded(
                    new String('bar'),
                    new Language('fr'),
                    new String('sub brand fr'),
                    new Description($descriptionFr)
                ))
                    ->withTitle(
                        new String('title fr')
                    )
                    ->withCopyright(
                        new String('copyright fr')
                    )
                    ->withUrl(
                        Url::fromNative('http://google.fr')
                    ),
            ],
            // Without optional parameters.
            'english optional parameters' => [
                [
                    'event_id' => 'foo',
                    'language' => 'en',
                    'sub_brand' => 'sub brand',
                    'description' => $descriptionEn,
                    'link_type' => 'collaboration',
                ],
                new CollaborationDataAdded(
                    new String('foo'),
                    new Language('en'),
                    new String('sub brand'),
                    new Description($descriptionEn)
                ),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param CollaborationDataAdded $linkAdded
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        CollaborationDataAdded $linkAdded
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
     * @param CollaborationDataAdded $expectedLinkAdded
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        CollaborationDataAdded $expectedLinkAdded
    ) {
        $this->assertEquals(
            $expectedLinkAdded,
            CollaborationDataAdded::deserialize($serializedValue)
        );
    }
}
