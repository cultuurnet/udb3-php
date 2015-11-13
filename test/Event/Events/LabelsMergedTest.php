<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use ValueObjects\String\String;

class LabelsMergedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_serialized_to_an_array()
    {
        $labelsMerged = new LabelsMerged(
            new String('foo'),
            new LabelCollection(
                [
                    new Label('label 1', true),
                    new Label('label 2', false),
                ]
            )
        );

        $this->assertInstanceOf(SerializableInterface::class, $labelsMerged);

        $expectedSerializedEvent = [
            'event_id' => 'foo',
            'labels' => [
                [
                    'text' => 'label 1',
                    'visible' => true,
                ],
                [
                    'text' => 'label 2',
                    'visible' => false,
                ],
            ],
        ];

        $this->assertEquals(
            $expectedSerializedEvent,
            $labelsMerged->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize_an_array()
    {
        $serializedEvent = [
            'event_id' => 'foo',
            'labels' => [
                [
                    'text' => 'label 1',
                    'visible' => true,
                ],
                [
                    'text' => 'label 2',
                    'visible' => false,
                ],
            ],
        ];

        $expectedLabelsMerged = new LabelsMerged(
            new String('foo'),
            new LabelCollection(
                [
                    new Label('label 1', true),
                    new Label('label 2', false),
                ]
            )
        );

        $this->assertEquals(
            $expectedLabelsMerged,
            LabelsMerged::deserialize($serializedEvent)
        );
    }
}
