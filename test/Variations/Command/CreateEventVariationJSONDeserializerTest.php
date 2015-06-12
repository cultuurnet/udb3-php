<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use ValueObjects\String\String;

class CreateEventVariationJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CreateEventVariationJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new CreateEventVariationJSONDeserializer();
    }

    /**
     * @test
     */
    public function it_validates_the_json()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid data');

        try {
            $this->deserializer->deserialize(new String('{"owner": "foo"}'));
        }
        catch(ValidationException $e) {
            $this->assertEquals(
                [
                    'the property purpose is required',
                    'the property same_as is required',
                    'the property description is required',
                ],
                $e->getErrors()
            );

            throw $e;
        }
    }

    /**
     * @test
     */
    public function it_returns_a_command()
    {
        $command = $this->deserializer->deserialize(
            new String(
                file_get_contents(__DIR__ . '/create-event-variations.json')
            )
        );

        $expectedCommand = new CreateEventVariation(
            new Url('//io.uitdatabank.be/event/a0a78cd3-df53-4359-97a3-04b3680e69a4'),
            new OwnerId('86142ebb-1c7a-4afe-8257-6b986eb5ca21'),
            new Purpose('personal'),
            new Description('My own personal description for this event')
        );

        $this->assertEquals(
            $expectedCommand,
            $command
        );
    }
}
