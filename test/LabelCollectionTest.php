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
    public function it_refuses_to_create_a_new_collection_with_a_keyword_already_contained_in_the_current_collection()
    {
        $labels = new LabelCollection(
            [
                new Label('keyword 1'),
            ]
        );

        $this->setExpectedException(\RuntimeException::class);

        $labels->with(new Label('keyword 1'));
    }
}
