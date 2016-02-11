<?php

namespace CultuurNet\UDB3;

use CultuurNet\Deserializer\MissingValueException;
use ValueObjects\String\String;

class LabelJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Label
     */
    private $label;

    /**
     * @var LabelJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->label = new Label('test-label');
        $this->deserializer = new LabelJSONDeserializer();
    }

    /**
     * @test
     */
    public function it_can_deserialize_a_valid_label()
    {
        $json = new String('{"label": "test-label"}');
        $label = $this->deserializer->deserialize($json);
        $this->assertEquals($this->label, $label);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_no_label_is_found()
    {
        $json = new String('{"foo": "bar"}');

        $this->setExpectedException(
            MissingValueException::class,
            'Missing value "label"!'
        );

        $this->deserializer->deserialize($json);
    }
}
