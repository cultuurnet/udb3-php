<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches\Command;

use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use ValueObjects\String\String;

class SubscribeToSavedSearchJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var String
     */
    protected $userId;

    /**
     * @var SubscribeToSavedSearchJSONDeserializer
     */
    protected $deserializer;

    public function setUp()
    {
        $this->userId = new String('xyx');
        $this->deserializer = new SubscribeToSavedSearchJSONDeserializer($this->userId);
    }

    /**
     * @test
     */
    public function it_creates_commands_with_the_user_id_passed_in_the_constructor()
    {
        $command = $this->deserializer->deserialize(
            $this->getStringFromFile('subscribe.json')
        );

        $this->assertEquals(
            new SubscribeToSavedSearch(
                $this->userId,
                new String('My very first saved search.'),
                new QueryString('city:"Leuven"')
            ),
            $command
        );
    }

    /**
     * @test
     */
    public function it_requires_a_query()
    {
        $this->setExpectedException(MissingValueException::class, 'query is missing');
        $this->deserializer->deserialize(
            $this->getStringFromFile('subscribe_without_query.json')
        );
    }

    /**
     * @test
     */
    public function it_requires_a_name()
    {
        $this->setExpectedException(MissingValueException::class, 'name is missing');
        $this->deserializer->deserialize(
            $this->getStringFromFile('subscribe_without_name.json')
        );
    }

    private function getStringFromFile($fileName)
    {
        $json = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        return new String($json);
    }
}
