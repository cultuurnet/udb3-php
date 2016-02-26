<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use CultuurNet\UDB3\UsedLabelsMemory\Created;
use CultuurNet\UDB3\UsedLabelsMemory\LabelUsed;
use ValueObjects\String\String;

class BackwardsCompatiblePayloadSerializerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SerializableInterface
     */
    protected $serializer;

    /**
     * @var string
     */
    private $sampleDir;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->serializer = BackwardsCompatiblePayloadSerializerFactory::createSerializer();

        $this->sampleDir = __DIR__ . '/samples/';
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_title_translated()
    {
        $dir = $this->sampleDir . 'serialized_event_title_translated_class.json';
        $this->checkKnowsNewNamespace($dir, TitleTranslated::class);
    }

    /**
     * @test
     */
    public function it_manipulates_the_item_id_of_event_title_translated()
    {
        $dir = $this->sampleDir . 'serialized_event_title_translated_class.json';
        $this->checkManipulatesItemId($dir);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_description_translated()
    {
        $dir = $this->sampleDir . 'serialized_event_description_translated_class.json';
        $this->checkKnowsNewNamespace($dir, DescriptionTranslated::class);
    }

    /**
     * @test
     */
    public function it_manipulates_the_item_id_of_event_description_translated()
    {
        $dir = $this->sampleDir . 'serialized_event_description_translated_class.json';
        $this->checkManipulatesItemId($dir);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_was_labelled()
    {
        $dir = $this->sampleDir . 'serialized_event_was_labelled_class.json';
        $this->checkKnowsNewNamespace($dir, LabelAdded::class);

    }

    public function it_manipulates_the_item_id_of_event_was_labelled()
    {
        $dir = $this->sampleDir . 'serialized_event_was_labelled_class.json';
        $this->checkManipulatesItemId($dir);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_was_tagged()
    {
        $dir = $this->sampleDir . 'serialized_event_was_tagged_class.json';
        $this->checkKnowsNewNamespace($dir, LabelAdded::class);
    }

    /**
     * @test
     */
    public function it_manipulates_the_item_id_of_event_was_tagged()
    {
        $dir = $this->sampleDir . 'serialized_event_was_tagged_class.json';
        $this->checkManipulatesItemId($dir);
    }

    /**
     * @test
     */
    public function it_manipulates_the_label_of_event_was_tagged()
    {
        $dir = $this->sampleDir . 'serialized_event_was_tagged_class.json';
        $this->checkManipulateLabel($dir);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_tag_erased()
    {
        $dir = $this->sampleDir . 'serialized_event_tag_erased_class.json';
        $this->checkKnowsNewNamespace($dir, LabelDeleted::class);
    }

    /**
     * @test
     */
    public function it_manipulates_the_item_id_of_event_tag_erased()
    {
        $dir = $this->sampleDir . 'serialized_event_tag_erased_class.json';
        $this->checkManipulatesItemId($dir);
    }

    /**
     * @test
     */
    public function it_manipulates_the_label_of_event_tag_erased()
    {
        $dir = $this->sampleDir . 'serialized_event_tag_erased_class.json';
        $this->checkManipulateLabel($dir);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_unlabelled()
    {
        $dir = $this->sampleDir . 'serialized_event_unlabelled_class.json';
        $this->checkKnowsNewNamespace($dir, LabelDeleted::class);
    }

    /**
     * @test
     */
    public function it_manipulates_the_item_id_of_event_unlabelled()
    {
        $dir = $this->sampleDir . 'serialized_event_unlabelled_class.json';
        $this->checkManipulatesItemId($dir);
    }

    /**
     * @test
     */
    public function it_converts_obsolete_labels_applied_to_labels_merged()
    {
        $serialized = file_get_contents(
            $this->sampleDir . 'serialized_labels_applied_class.json'
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

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_used_keywords_memory_created()
    {
        $dir = $this->sampleDir . 'serialized_used_keywords_memory_created.json';
        $this->checkKnowsNewNamespace($dir, Created::class);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_used_keywords_memory_keyword_used()
    {
        $dir = $this->sampleDir . 'serialized_used_keyword_memory_used.json';
        $this->checkKnowsNewNamespace($dir, LabelUsed::class);
    }

    /**
     * @test
     */
    public function it_manipulated_the_label_of_used_keywords_memory_keyword_used()
    {
        $dir = $this->sampleDir . 'serialized_used_keyword_memory_used.json';
        $this->checkManipulateLabel($dir);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_imported_from_udb2_class()
    {
        $serialized = file_get_contents($this->sampleDir . 'serialized_event_imported_from_udb2_class.json');
        $decoded = json_decode($serialized, true);

        $importedFromUDB2 = $this->serializer->deserialize($decoded);

        $this->assertInstanceOf(EventImportedFromUDB2::class, $importedFromUDB2);
    }

    /**
     * @test
     */
    public function it_manipulates_the_item_id_of_event_booking_info_updated()
    {
        $dir = $this->sampleDir . 'serialized_event_booking_info_updated_class.json';
        $this->checkManipulatesItemId($dir);
    }

    /**
     * @test
     */
    public function it_manipulates_the_item_id_of_event_typical_age_range_deleted()
    {
        $dir = $this->sampleDir . 'serialized_event_typical_age_range_deleted_class.json';
        $this->checkManipulatesItemId($dir);
    }

    /**
     * @test
     */
    public function it_manipulates_the_item_id_of_event_contact_point_updated()
    {
        $dir = $this->sampleDir . 'serialized_event_contact_point_updated_class.json';
        $this->checkManipulatesItemId($dir);
    }

    /**
     * @test
     */
    public function it_manipulates_the_item_id_of_event_major_info_updated()
    {
        $dir = $this->sampleDir . 'serialized_event_major_info_updated_class.json';
        $this->checkManipulatesItemId($dir);
    }

    /**
     * @param string $dir
     */
    private function checkManipulatesItemId($dir)
    {
        $serialized = file_get_contents($dir);
        $decoded = json_decode($serialized, true);
        $eventId = $decoded['payload']['event_id'];

        /**
         * @var AbstractEvent $titleTranslated
         */
        $abstractEvent = $this->serializer->deserialize($decoded);
        $itemId = $abstractEvent->getItemId();

        $this->assertEquals($eventId, $itemId);
    }

    /**
     * @param string $dir
     */
    private function checkManipulateLabel($dir)
    {
        $serialized = file_get_contents($dir);
        $decoded = json_decode($serialized, true);
        $keyword = $decoded['payload']['keyword'];

        /**
         * @var AbstractLabelEvent $labelAdded
         */
        $abstractLabelEvent = $this->serializer->deserialize($decoded);
        $label = $abstractLabelEvent->getLabel();

        $this->assertEquals($keyword, $label);
    }

    /**
     * @param string $dir
     * @param $expectedClass
     */
    private function checkKnowsNewNamespace($dir, $expectedClass)
    {
        $serialized = file_get_contents($dir);
        $decoded = json_decode($serialized, true);

        $newEvent = $this->serializer->deserialize($decoded);

        $this->assertInstanceOf($expectedClass, $newEvent);
    }
}
