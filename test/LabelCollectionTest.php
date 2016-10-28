<?php

namespace CultuurNet\UDB3;

class LabelCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function notALabelProvider()
    {
        return [
            ['keyword 1'],
            [null],
            [1],
            [[]],
            [new \stdClass()],
        ];
    }

    /**
     * @test
     * @dataProvider notALabelProvider
     * @param mixed $notALabel
     */
    public function it_can_only_contain_labels($notALabel)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        new LabelCollection(
            [
                $notALabel,
                new Label('foo'),
            ]
        );
    }

    /**
     * @test
     */
    public function it_ignores_keywords_already_contained_in_the_current_collection()
    {
        $existingCollection = new LabelCollection(
            [
                new Label('keyword 1'),
            ]
        );

        $unchangedCollection = $existingCollection->with(new Label('keyword 1'));
        $this->assertEquals($existingCollection, $unchangedCollection);
    }

    /**
     * @test
     */
    public function it_ignores_invalid_labels_when_creating_from_string_array()
    {
        $labelsAsStrings = [
            'Correct label',
            'F',
            'This label is much too long and will also be ignored, just like the label F which is too short. But a few more extra characters are needed to make it fail! Like many aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'Another correct label'
        ];

        $labelCollection = LabelCollection::fromStrings($labelsAsStrings);

        $expectedLabelCollection = new LabelCollection([
            new Label('Correct label'),
            new Label('Another correct label')
        ]);

        $this->assertEquals($expectedLabelCollection, $labelCollection);
    }
}
