<?php

namespace CultuurNet\UDB3\Label\ReadModels\Helper;

use CultuurNet\UDB3\Event\Events\LabelAdded as EventLabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted as EventLabelDeleted;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Place\Events\LabelAdded as PlaceLabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted as PlaceLabelDeleted;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class AbstractLabelEventHelperTest extends \PHPUnit_Framework_TestCase
{
    const RELATION_ID = 'relationId';

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readRepository;

    /**
     * @var AbstractLabelEventHelper
     */
    private $abstractLabelEventHelper;

    /**
     * @var AbstractLabelEvent
     */
    private $abstractLabelEvent;

    /**
     * @var StringLiteral
     */
    private $labelName;

    /**
     * @var Label
     */
    private $label;

    protected function setUp()
    {
        $this->readRepository = $this->getMock(ReadRepositoryInterface::class);

        $this->abstractLabelEventHelper = new AbstractLabelEventHelper($this->readRepository);

        $this->labelName = new StringLiteral('labelName');
        $this->label = new Label($this->labelName->toNative());

        $this->abstractLabelEvent = $this->getMockForAbstractClass(
            AbstractLabelEvent::class,
            [self::RELATION_ID, $this->label]
        );
    }

    /**
     * @test
     */
    public function it_can_get_the_uuid()
    {
        $expectedUuid = new UUID();

        $this->readRepository->method('getByName')
            ->with($this->labelName)
            ->willReturn(new Entity(
                $expectedUuid,
                $this->labelName,
                Visibility::VISIBLE(),
                Privacy::PRIVACY_PUBLIC()
            ));

        $actualUuid = $this->abstractLabelEventHelper->getUuid($this->abstractLabelEvent);

        $this->assertEquals($expectedUuid, $actualUuid);
    }

    /**
     * @test
     */
    public function it_can_get_the_relation_type_from_event_label_added()
    {
        $eventLabelAdded = new EventLabelAdded(self::RELATION_ID, $this->label);

        $relationType = $this->abstractLabelEventHelper->getRelationType($eventLabelAdded);

        $this->assertEquals(RelationType::EVENT(), $relationType);
    }

    /**
     * @test
     */
    public function it_can_get_the_relation_type_from_event_label_deleted()
    {
        $eventLabelDeleted = new EventLabelDeleted(self::RELATION_ID, $this->label);

        $relationType = $this->abstractLabelEventHelper->getRelationType($eventLabelDeleted);

        $this->assertEquals(RelationType::EVENT(), $relationType);
    }

    /**
     * @test
     */
    public function it_can_get_the_relation_type_from_place_label_added()
    {
        $placeLabelAdded = new PlaceLabelAdded(self::RELATION_ID, $this->label);

        $relationType = $this->abstractLabelEventHelper->getRelationType($placeLabelAdded);

        $this->assertEquals(RelationType::PLACE(), $relationType);
    }

    /**
     * @test
     */
    public function it_can_get_the_relation_type_from_place_label_deleted()
    {
        $placeLabelDeleted = new PlaceLabelDeleted(self::RELATION_ID, $this->label);

        $relationType = $this->abstractLabelEventHelper->getRelationType($placeLabelDeleted);

        $this->assertEquals(RelationType::PLACE(), $relationType);
    }

    /**
     * @test
     */
    public function it_can_get_the_relation_id()
    {
        $relationId = $this->abstractLabelEventHelper->getRelationId($this->abstractLabelEvent);

        $this->assertEquals(self::RELATION_ID, $relationId);
    }
}
