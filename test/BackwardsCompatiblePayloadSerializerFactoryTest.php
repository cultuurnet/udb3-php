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
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\UsedLabelsMemory\Created;
use CultuurNet\UDB3\UsedLabelsMemory\LabelUsed;
use PHPUnit_Framework_TestCase;
use ValueObjects\String\String;

class BackwardsCompatiblePayloadSerializerFactoryTest extends PHPUnit_Framework_TestCase
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
        $sampleFile = $this->sampleDir . 'serialized_event_title_translated_class.json';
        $this->assertClass($sampleFile, TitleTranslated::class);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_title_translated()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_title_translated_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_description_translated()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_description_translated_class.json';
        $this->assertClass($sampleFile, DescriptionTranslated::class);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_description_translated()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_description_translated_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_was_labelled()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_was_labelled_class.json';
        $this->assertClass($sampleFile, LabelAdded::class);

    }

    public function it_replaces_event_id_with_item_id_on_event_was_labelled()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_was_labelled_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_was_tagged()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_was_tagged_class.json';
        $this->assertClass($sampleFile, LabelAdded::class);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_was_tagged()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_was_tagged_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_replaces_keyword_with_label_on_event_was_tagged()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_was_tagged_class.json';
        $this->assertKeywordReplacedWithLabel($sampleFile);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_tag_erased()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_tag_erased_class.json';
        $this->assertClass($sampleFile, LabelDeleted::class);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_tag_erased()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_tag_erased_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_replaces_keyword_with_label_on_event_tag_erased()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_tag_erased_class.json';
        $this->assertKeywordReplacedWithLabel($sampleFile);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_event_unlabelled()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_unlabelled_class.json';
        $this->assertClass($sampleFile, LabelDeleted::class);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_unlabelled()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_unlabelled_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
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
        $sampleFile = $this->sampleDir . 'serialized_used_keywords_memory_created.json';
        $this->assertClass($sampleFile, Created::class);
    }

    /**
     * @test
     */
    public function it_knows_the_new_namespace_of_used_keywords_memory_keyword_used()
    {
        $sampleFile = $this->sampleDir . 'serialized_used_keyword_memory_used.json';
        $this->assertClass($sampleFile, LabelUsed::class);
    }

    /**
     * @test
     */
    public function it_manipulated_the_label_of_used_keywords_memory_keyword_used()
    {
        $sampleFile = $this->sampleDir . 'serialized_used_keyword_memory_used.json';
        $this->assertKeywordReplacedWithLabel($sampleFile);
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
    public function it_replaces_event_id_with_item_id_on_event_booking_info_updated()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_booking_info_updated_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_typical_age_range_deleted()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_typical_age_range_deleted_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_typical_age_range_updated()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_typical_age_range_updated_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_contact_point_updated()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_contact_point_updated_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_major_info_updated()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_major_info_updated_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_organizer_updated()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_organizer_updated_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on__event_organizer_deleted()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_organizer_deleted_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @test
     */
    public function it_replaces_event_id_with_item_id_on_event_description_updated()
    {
        $sampleFile = $this->sampleDir . 'serialized_event_description_updated_class.json';
        $this->assertEventIdReplacedWithItemId($sampleFile);
    }

    /**
     * @param string $sampleFile
     */
    private function assertEventIdReplacedWithItemId($sampleFile)
    {
        $serialized = file_get_contents($sampleFile);
        $decoded = json_decode($serialized, true);
        $eventId = $decoded['payload']['event_id'];

        /**
         * @var AbstractEvent $abstractEvent
         */
        $abstractEvent = $this->serializer->deserialize($decoded);
        $itemId = $abstractEvent->getItemId();

        $this->assertEquals($eventId, $itemId);
    }

    /**
     * @param string $sampleFile
     */
    private function assertKeywordReplacedWithLabel($sampleFile)
    {
        $serialized = file_get_contents($sampleFile);
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
     * @param string $sampleFile
     * @param $expectedClass
     */
    private function assertClass($sampleFile, $expectedClass)
    {
        $serialized = file_get_contents($sampleFile);
        $decoded = json_decode($serialized, true);

        $newEvent = $this->serializer->deserialize($decoded);

        $this->assertInstanceOf($expectedClass, $newEvent);
    }
}
