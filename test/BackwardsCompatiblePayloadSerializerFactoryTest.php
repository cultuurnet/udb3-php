<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;

class BackwardsCompatiblePayloadSerializerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SerializableInterface
     */
    protected $serializer;

    public function setUp()
    {
        parent::setUp();
        $this->serializer = BackwardsCompatiblePayloadSerializerFactory::createSerializer();
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_imported_from_udb2_class()
    {
        $serialized = file_get_contents(__DIR__ . '/samples/serialized_event_imported_from_udb2_class.json');
        $decoded = json_decode($serialized, true);

        $importedFromUDB2 = $this->serializer->deserialize($decoded);

        $this->assertInstanceOf(EventImportedFromUDB2::class, $importedFromUDB2);
    }
}
