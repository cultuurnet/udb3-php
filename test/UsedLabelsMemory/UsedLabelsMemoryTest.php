<?php


namespace CultuurNet\UDB3\UsedLabelsMemory;

use CultuurNet\UDB3\Label;

class UsedLabelsMemoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var UsedLabelsMemory
     */
    protected $memory;

    public function setUp()
    {
        $this->memory = new UsedLabelsMemory();
    }

    /**
     * @test
     */
    public function it_adds_used_labels_to_the_top_of_the_list()
    {
        $label = new Label('use-me');
        $this->memory->labelUsed($label);

        $usedLabels = $this->memory->getLabels();

        $this->assertEquals($label, $usedLabels[0]);
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_maximum_ten_last_used_labels()
    {
        $labels = [
            new Label('Label-1'),
            new Label('Label-2'),
            new Label('Label-3'),
            new Label('Label-4'),
            new Label('Label-5'),
            new Label('Label-6'),
            new Label('Label-7'),
            new Label('Label-8'),
            new Label('Label-9'),
            new Label('Label-10'),
            new Label('Label-11'),
        ];

        foreach ($labels as $label) {
            $this->memory->labelUsed($label);
        }

        $usedLabels = $this->memory->getLabels();

        $iLabel = 0;
        $listLength = 10;
        $reverseLabels = array_reverse($labels);

        $this->assertEquals(count($usedLabels), 10);

        while ($iLabel < $listLength) {
            $this->assertEquals(
                $reverseLabels[$iLabel],
                $usedLabels[$iLabel]
            );
            $iLabel++;
        };
    }

    /**
     * @test
     */
    public function it_pushes_an_already_used_label_to_the_top_of_the_list_when_used_again()
    {
        $labels = [
            new Label('label-1'),
            new Label('label-2'),
            new Label('label-3'),
        ];

        foreach ($labels as $label) {
            $this->memory->labelUsed($label);
        };

        $this->memory->labelUsed(new Label('label-2'));

        $usedLabels = $this->memory->getLabels();

        $this->assertEquals(
            [
                new Label('label-2'),
                new Label('label-3'),
                new Label('label-1'),

            ],
            $usedLabels
        );
    }

    /**
     * @test
     */
    public function it_only_adds_a_label_once()
    {
        $this->memory->labelUsed(new Label('label-1'));
        $this->memory->labelUsed(new Label('label-1'));

        $usedLabels = $this->memory->getLabels();

        $this->assertEquals([new Label('label-1')], $usedLabels);

        $this->assertCount(
            1,
            $this->memory->getUncommittedEvents()->getIterator()
        );
    }

    /**
     * @test
     */
    public function it_can_be_serialized_to_a_json_array()
    {
        $this->memory->labelUsed(new Label('label-1'));
        $this->memory->labelUsed(new Label('label-2'));

        $serializedMemory = json_encode($this->memory);

        $this->assertEquals(
            '["label-2","label-1"]',
            $serializedMemory
        );
    }
}
