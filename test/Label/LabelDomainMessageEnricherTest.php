<?php

namespace CultuurNet\UDB3\Label;

use Broadway\Domain\DateTime as BroaddwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\DomainMessage\DomainMessageTestDataTrait;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadePrivate;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class LabelDomainMessageEnricherTest extends \PHPUnit_Framework_TestCase
{
    use DomainMessageTestDataTrait;

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readRepository;

    /**
     * @var LabelDomainMessageEnricher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $enricher;

    /**
     * @var Entity
     */
    private $label;

    /**
     * @var DomainMessage
     */
    private $domainMessage;

    public function setUp()
    {
        $this->readRepository = $this->getMock(ReadRepositoryInterface::class);

        $this->enricher = new LabelDomainMessageEnricher($this->readRepository);

        $uuid = new UUID();

        $this->label = new Entity(
            $uuid,
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC()
        );

        $this->domainMessage = new DomainMessage(
            $uuid->toNative(),
            1,
            new Metadata(),
            new MadeVisible($uuid),
            BroaddwayDateTime::now()
        );
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
    public function it_enriches_visibility_events()
    {
        $this->mockGetByUuid($this->label);

        $expectedDomainMessage = $this->domainMessage->andMetadata(
            new Metadata([
                LabelDomainMessageEnricher::LABEL_NAME => $this->label->getName()
            ])
        );

        $actualDomainMessage = $this->enricher->enrich($this->domainMessage);

        $this->assertEquals($expectedDomainMessage, $actualDomainMessage);
    }

    /**
     * @test
     */
    public function it_does_not_enrich_unsupported_events()
    {
        $unsupported = $this->createDomainMessage($this, MadePrivate::class);
        $enriched = $this->enricher->enrich($unsupported);

        $this->assertEquals($unsupported, $enriched);
    }

    /**
     * @test
     */
    public function it_does_not_enrich_when_label_can_not_be_resolved()
    {
        $this->mockGetByUuid(null);

        $expectedDomainMessage = $this->domainMessage;

        $actualDomainMessage = $this->enricher->enrich($this->domainMessage);

        $this->assertEquals($expectedDomainMessage, $actualDomainMessage);
    }

    /**
     * @param Entity|null $label
     */
    private function mockGetByUuid(Entity $label = null)
    {
        $this->readRepository->expects($this->once())
            ->method('getByUuid')
            ->willReturn($label);
    }
}
