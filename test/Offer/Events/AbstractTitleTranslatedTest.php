<?php

namespace CultuurNet\UDB3\Offer\Events;


use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Item\Events\TitleTranslated;
use ValueObjects\String\String;

class AbstractTitleTranslatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractTitleTranslated
     */
    protected $titleTranslatedEvent;

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
        $this->titleTranslatedEvent = new TitleTranslated($this->itemId, $this->language, $this->title);
    }

    /**
     * @test
     */
    public function it_can_be_instantiated_With_properties()
    {
        $expectedItemId = 'Foo';
        $expectedLanguage = new Language('en');
        $expectedTitle = new String('Title');
        $expectedTitleTranslated = new TitleTranslated(
            $expectedItemId,
            $expectedLanguage,
            $expectedTitle
        );

        $this->assertEquals($expectedTitleTranslated, $this->titleTranslatedEvent);
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $expectedItemId = 'Foo';
        $expectedLanguage = new Language('en');
        $expectedTitle = new String('Title');

        $itemId = $this->titleTranslatedEvent->getItemId();
        $language = $this->titleTranslatedEvent->getLanguage();
        $title = $this->titleTranslatedEvent->getTitle();

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
            'abstractTitleTranslated' => [
                [
                    'item_id' => 'madId',
                    'language' => 'en',
                    'title' => 'Title',
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
