<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\DomainMessage\DomainMessageTestDataTrait;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use ValueObjects\Identity\UUID;

class OrganizerLabelDomainMessageEnricherTest extends \PHPUnit_Framework_TestCase
{
    use DomainMessageTestDataTrait;

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readRepository;

    /**
     * @var OrganizerLabelDomainMessageEnricher
     */
    private $enricher;


    public function setUp()
    {
        $this->readRepository = $this->getMock(ReadRepositoryInterface::class);

        $this->enricher = new OrganizerLabelDomainMessageEnricher($this->readRepository);
    }

    /**
     * @test
     */
    public function it_supports_label_added_event()
    {
        $supported = $this->createDomainMessage($this, LabelAdded::class);
        $this->assertTrue($this->enricher->supports($supported));
    }

    /**
     * @test
     */
    public function it_supports_label_removed_event()
    {
        $supported = $this->createDomainMessage($this, LabelRemoved::class);
        $this->assertTrue($this->enricher->supports($supported));
    }

    /**
     * @test
     */
    public function it_does_not_support_events_other_then_label_events()
    {
        $supported = $this->createDomainMessage($this, OrganizerDeleted::class);
        $this->assertFalse($this->enricher->supports($supported));
    }

    /**
     * @test
     */
    public function it_returns_uuid_from_supported_domain_message()
    {
        $labelId = new UUID();

        $domainMessage = new DomainMessage(
            $labelId,
            1,
            new Metadata(),
            new LabelAdded('organizerId', $labelId),
            BroadwayDateTime::now()
        );

        $uuid = $this->enricher->getLabelUuid($domainMessage);

        $this->assertEquals($labelId, $uuid);
    }
}
