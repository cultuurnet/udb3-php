<?php

namespace CultuurNet\UDB3\Label;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\DomainMessage\DomainMessageTestDataTrait;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadePrivate;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use ValueObjects\Identity\UUID;

class LabelDomainMessageEnricherTest extends \PHPUnit_Framework_TestCase
{
    use DomainMessageTestDataTrait;

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readRepository;

    /**
     * @var LabelDomainMessageEnricher
     */
    private $enricher;


    public function setUp()
    {
        $this->readRepository = $this->getMock(ReadRepositoryInterface::class);

        $this->enricher = new LabelDomainMessageEnricher($this->readRepository);
    }

    /**
     * @test
     */
    public function it_supports_made_visible_event()
    {
        $supported = $this->createDomainMessage($this, MadeVisible::class);
        $this->assertTrue($this->enricher->supports($supported));
    }

    /**
     * @test
     */
    public function it_supports_made_invisible_event()
    {
        $supported = $this->createDomainMessage($this, MadeInvisible::class);
        $this->assertTrue($this->enricher->supports($supported));
    }

    /**
     * @test
     */
    public function it_does_not_support_events_other_then_visibility()
    {
        $supported = $this->createDomainMessage($this, MadePrivate::class);
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
            new MadeVisible($labelId),
            BroadwayDateTime::now()
        );

        $uuid = $this->enricher->getLabelUuid($domainMessage);

        $this->assertEquals($labelId, $uuid);
    }
}
