<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelRemoved;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Label\Events\AbstractEvent;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\UsedLabelsMemory\Created;
use CultuurNet\UDB3\UsedLabelsMemory\LabelUsed;
use PHPUnit_Framework_TestCase;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class BackwardsCompatiblePayloadSerializerFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SerializableInterface
     */
    protected $serializer;

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelRepository;

    /**
     * @var string
     */
    private $sampleDir;

    public function setUp()
    {
        parent::setUp();

        $this->labelRepository = $this->getMock(ReadRepositoryInterface::class);
        $this->labelRepository->method('getByUuid')
            ->with('86c5b0f4-a5da-4a81-815f-3839634c212c')
            ->willReturn(
                new Entity(
                    new UUID('86c5b0f4-a5da-4a81-815f-3839634c212c'),
                    new LabelName('2dotstwice'),
                    Visibility::INVISIBLE(),
                    Privacy::PRIVACY_PRIVATE()
                )
            );

        $this->serializer = BackwardsCompatiblePayloadSerializerFactory::createSerializer(
            $this->labelRepository
        );

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
    public function it_adds_label_name_on_made_invisible_event()
    {
        $sampleFile = $this->sampleDir . 'serialized_label_was_made_invisible.json';
        $this->assertLabelNameAdded($sampleFile);
    }

    /**
     * @test
     */
    public function it_adds_label_name_on_made_visible_event()
    {
        $sampleFile = $this->sampleDir . 'serialized_label_was_made_visible.json';
        $this->assertLabelNameAdded($sampleFile);
    }

    /**
     * @test
     */
    public function it_adds_label_name_on_made_private_event()
    {
        $sampleFile = $this->sampleDir . 'serialized_label_was_made_private.json';
        $this->assertLabelNameAdded($sampleFile);
    }

    /**
     * @test
     */
    public function it_adds_label_name_on_made_public_event()
    {
        $sampleFile = $this->sampleDir . 'serialized_label_was_made_public.json';
        $this->assertLabelNameAdded($sampleFile);
    }

    /**
     * @test
     */
    public function it_adds_label_name_and_visibility_on_label_added_to_organizer_event()
    {
        $sampleFile = $this->sampleDir . 'serialized_label_was_added_to_organizer.json';
        $this->assertOrganizerLabelEventFixed($sampleFile);
    }

    /**
     * @test
     */
    public function it_adds_label_visibility_on_label_added_to_organizer_event_with_label()
    {
        $sampleFile = $this->sampleDir . 'serialized_label_was_added_to_organizer_with_label.json';
        $this->assertOrganizerLabelEventFixed($sampleFile);
    }

    /**
     * @test
     */
    public function it_adds_label_name_on_label_added_to_organizer_event_with_visibility()
    {
        $sampleFile = $this->sampleDir . 'serialized_label_was_added_to_organizer_with_visibility.json';
        $this->assertOrganizerLabelEventFixed($sampleFile);
    }

    /**
     * @test
     */
    public function it_does_not_modify_label_added_to_organizer_event_with_label_and_visibility()
    {
        $sampleFile = $this->sampleDir . 'serialized_label_was_added_to_organizer_with_label_and_visibility.json';

        $this->labelRepository->expects($this->never())
            ->method('getByUuid');

        $serialized = file_get_contents($sampleFile);
        $decoded = json_decode($serialized, true);
        $this->serializer->deserialize($decoded);
    }

    /**
     * @test
     */
    public function it_adds_label_name_and_visibility_on_label_removed_from_organizer_event()
    {
        $sampleFile = $this->sampleDir . 'serialized_label_was_removed_from_organizer.json';
        $this->assertOrganizerLabelEventFixed($sampleFile);
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
        $this->assertClass($sampleFile, LabelRemoved::class);
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
        $this->assertClass($sampleFile, LabelRemoved::class);
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
     * @dataProvider typedIdPlaceEventClassProvider
     * @param string $eventClassFile
     */
    public function it_should_replace_place_id_on_older_events_with_item_id(
        $eventClassFile
    ) {
        $sampleFile = $this->sampleDir . '/place/'. $eventClassFile;
        $this->assertPlaceIdReplacedWithItemId($sampleFile);
    }

    public function typedIdPlaceEventClassProvider()
    {
        return [
            ['booking_info_updated.class.json'],
            ['contact_point_updated.class.json'],
            ['description_updated.class.json'],
            ['organizer_updated.class.json'],
            ['organizer_deleted.class.json'],
            ['typical_age_range_deleted.class.json'],
            ['typical_age_range_updated.class.json'],
        ];
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
        $this->assertTypedIdReplacedWithItemId('event', $sampleFile);
    }

    /**
     * @param string $sampleFile
     */
    private function assertPlaceIdReplacedWithItemId($sampleFile)
    {
        $this->assertTypedIdReplacedWithItemId('place', $sampleFile);
    }

    /**
     * @param string $type
     * @param $sampleFile
     */
    private function assertTypedIdReplacedWithItemId($type, $sampleFile)
    {
        $serialized = file_get_contents($sampleFile);
        $decoded = json_decode($serialized, true);
        $typedId = $decoded['payload'][$type . '_id'];

        /**
         * @var AbstractEvent $abstractEvent
         */
        $abstractEvent = $this->serializer->deserialize($decoded);
        $itemId = $abstractEvent->getItemId();

        $this->assertEquals($typedId, $itemId);
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

    /**
     * @param string $sampleFile
     */
    private function assertLabelNameAdded($sampleFile)
    {
        $serialized = file_get_contents($sampleFile);
        $decoded = json_decode($serialized, true);

        /** @var AbstractEvent $labelEvent */
        $labelEvent = $this->serializer->deserialize($decoded);

        $this->assertEquals('2dotstwice', $labelEvent->getName()->toNative());
    }

    /**
     * @param string $sampleFile
     */
    private function assertOrganizerLabelEventFixed($sampleFile)
    {
        $serialized = file_get_contents($sampleFile);
        $decoded = json_decode($serialized, true);

        /** @var LabelEventInterface $labelEvent */
        $labelEvent = $this->serializer->deserialize($decoded);

        $this->assertEquals('2dotstwice', (string) $labelEvent->getLabel());
        $this->assertFalse($labelEvent->getLabel()->isVisible());
    }
}
