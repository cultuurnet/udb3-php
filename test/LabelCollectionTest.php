<?php
namespace CultuurNet\UDB3;

/**
 * @file
 */
class LabelCollectionTest extends \PHPUnit_Framework_TestCase
{
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
}
