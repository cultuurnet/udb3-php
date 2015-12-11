<?php

namespace test\Event\Events;

use CultuurNet\UDB3\CollaborationData;
use CultuurNet\UDB3\Event\Events\CollaborationDataAdded;
use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class CollaborationDataAddedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_its_properties()
    {
        $eventId = new String('foo');
        $lang = new Language('en');
        $data = new CollaborationData(
            new String('sub brand')
        );

        $added = new CollaborationDataAdded(
            $eventId,
            $lang,
            $data
        );

        $this->assertEquals($eventId, $added->getEventId());
        $this->assertEquals($lang, $added->getLanguage());
        $this->assertEquals($data, $added->getCollaborationData());
    }

    public function serializationDataProvider()
    {
        $enCollaborationData = [
            'subBrand' => 'sub brand',
            'title' => 'title',
            'text' => 'description EN',
            'copyright' => 'copyright',
            'keyword' => 'Lorem',
            'image' => '/image.en.png',
            'article' => 'Ipsum',
            'link' => 'http://google.com',
        ];

        $frCollaborationData = [
            'subBrand' => 'sub brand fr',
            'title' => 'title fr',
            'text' => 'text fr',
            'copyright' => 'copyright fr',
            'keyword' => 'Lorèm',
            'image' => '/image.fr.png',
            'article' => 'Ipsume',
            'link' => 'http://google.fr',
        ];

        return [
            // English.
            [
                [
                    'eventId' => 'foo',
                    'language' => 'en',
                    'collaborationData' => $enCollaborationData,
                ],
                new CollaborationDataAdded(
                    new String('foo'),
                    new Language('en'),
                    CollaborationData::deserialize(
                        $enCollaborationData
                    )
                ),
            ],
            // French.
            [
                [
                    'eventId' => 'bar',
                    'language' => 'fr',
                    'collaborationData' => $frCollaborationData,
                ],
                new CollaborationDataAdded(
                    new String('bar'),
                    new Language('fr'),
                    CollaborationData::deserialize(
                        $frCollaborationData
                    )
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
