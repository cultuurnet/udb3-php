<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Item\Commands\TranslateTitle;
use CultuurNet\UDB3\Offer\Item\Events\TitleTranslated;
use ValueObjects\String\String;

class AbstractPropertyTranslatedEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractPropertyTranslatedEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $propertyTranslatedEvent;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var String
     */
    protected $title;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->language = new Language('en');
        $this->title = new String('Title');
        $this->propertyTranslatedEvent = new TranslateTitle($this->itemId, $this->language, $this->title);
    }

    /**
     * @test
     */
    public function it_can_be_instantiated_With_properties()
    {
        $expectedItemId = 'Foo';
        $expectedLanguage = new Language('en');
        $expectedTitle = new String('Title');
        $expectedTranslateEvent = new TranslateTitle($expectedItemId, $expectedLanguage, $expectedTitle);

        $this->assertEquals($expectedTranslateEvent, $this->propertyTranslatedEvent);
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $expectedItemId = 'Foo';
        $expectedLanguage = new Language('en');
        $expectedTitle = new String('Title');

        $itemId = $this->propertyTranslatedEvent->getItemId();
        $language = $this->propertyTranslatedEvent->getLanguage();
        $title = $this->propertyTranslatedEvent->getTitle();

        $this->assertEquals($expectedItemId, $itemId);
        $this->assertEquals($expectedLanguage, $language);
        $this->assertEquals($expectedTitle, $title);
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param $expectedSerializedValue
     * @param TitleTranslated $titleTranslated
     */
    public function it_can_be_serialized_to_an_array(
        $expectedSerializedValue,
        TitleTranslated $titleTranslated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $titleTranslated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param $serializedValue
     * @param TitleTranslated $expectedTitleTranslated
     */
    public function it_can_deserialize_an_array(
        $serializedValue,
        TitleTranslated $expectedTitleTranslated
    ) {
        $this->assertEquals(
            $expectedTitleTranslated,
            TitleTranslated::deserialize($serializedValue)
        );
    }

    /**
     * @return array
     */
    public function serializationDataProvider()
    {
        return [
            'abstractPropertyTranslatedEvent' => [
                [
                    'item_id' => 'madId',
                    'language' => 'en',
                    'title' => 'Title'
                ],
                new TitleTranslated(
                    'madId',
                    new Language('en'),
                    new String('Title')
                ),
            ],
        ];
    }
}
