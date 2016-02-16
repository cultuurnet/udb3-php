<?php

namespace CultuurNet\UDB3;

use CultuurNet\Deserializer\MissingValueException;
use ValueObjects\String\String;

class TitleJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TitleJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new TitleJSONDeserializer();
    }

    /**
     * @test
     */
    public function it_can_deserialize_a_valid_title()
    {
        $json = new String('{"title": "Lorem ipsum"}');
        $expected = new Title("Lorem ipsum");
        $actual = $this->deserializer->deserialize($json);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_a_title_is_missing()
    {
        $json = new String('{"foo": "bar"}');

        $this->setExpectedException(
            MissingValueException::class,
            'Missing value for "title".'
        );

        $this->deserializer->deserialize($json);
    }
}
