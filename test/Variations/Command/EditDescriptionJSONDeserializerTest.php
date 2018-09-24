<?php

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class EditDescriptionJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EditDescriptionJSONDeserializer
     */
    private $deserializer;

    /**
     * @var Id
     */
    private $variationId;

    public function setUp()
    {
        $this->variationId = new Id(UUID::generateAsString());
        $this->deserializer = new EditDescriptionJSONDeserializer(
            $this->variationId
        );
    }

    /**
     * @test
     */
    public function it_deserializes_a_json_command()
    {
        $jsonData = '{"description": "An edited description."}';
        $jsonCommand = new StringLiteral($jsonData);
        $deserializedCommand = $this->deserializer->deserialize($jsonCommand);

        $expectedCommand = new EditDescription(
            $this->variationId,
            new Description('An edited description.')
        );

        $this->assertEquals($expectedCommand, $deserializedCommand);
    }

    /**
     * @test
     */
    public function it_validates_the_json_command()
    {
        $jsonData = '{"unwanted": "What am I doing here."}';
        $jsonCommand = new StringLiteral($jsonData);

        try {
            $this->deserializer->deserialize($jsonCommand);
        } catch (ValidationException $e) {
            $this->assertEquals(
                [
                    'the property description is required',
                    'The property - unwanted - is not defined and the definition does not allow additional properties',
                ],
                $e->getErrors()
            );
        }
    }
}
