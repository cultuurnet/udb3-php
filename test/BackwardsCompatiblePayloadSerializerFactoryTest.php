<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use ValueObjects\String\String;

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

    /**
     * @test
     */
    public function it_converts_obsolete_labels_applied_to_labels_merged()
    {
        $serialized = file_get_contents(
            __DIR__ . '/samples/serialized_labels_applied_class.json'
        );
        $decoded = json_decode($serialized, true);

        $labelsMerged = $this->serializer->deserialize($decoded);

        $this->assertInstanceOf(LabelsMerged::class, $labelsMerged);

        $this->assertEquals(
            new LabelsMerged(
                new String('24b1e348-f27d-4f70-ae1a-871074267c2e'),
                new LabelCollection(
                    [
                        new Label('keyword 1', true),
                        new Label('keyword 2', false),
                    ]
                )
            ),
            $labelsMerged
        );
    }
}
