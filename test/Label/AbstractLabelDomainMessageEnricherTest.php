<?php

namespace CultuurNet\UDB3\Label;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class AbstractLabelDomainMessageEnricherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelReadRepository;

    /**
     * @var AbstractLabelDomainMessageEnricher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $enricher;

    /**
     * @var DomainMessage
     */
    private $domainMessage;

    /**
     * @var Entity
     */
    private $label;

    protected function setUp()
    {
        $this->labelReadRepository = $this->getMock(
            ReadRepositoryInterface::class
        );

        $this->enricher = $this->getMockForAbstractClass(
            AbstractLabelDomainMessageEnricher::class,
            [$this->labelReadRepository]
        );

        $this->label = new Entity(
            new UUID(),
            new StringLiteral('2dotstwice'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC()
        );

        $this->domainMessage = new DomainMessage(
            new UUID(),
            1,
            new Metadata(),
            null,
            BroadwayDateTime::now()
        );
    }

    /**
     * @test
     */
    public function it_enriches_supported_messages()
    {
        $expectedDomainMessage = $this->domainMessage->andMetadata(
            new Metadata(['labelName' => $this->label->getName()->toNative()])
        );

        $this->mockSupports(true);

        $this->mockGetLabelUuid($this->label->getUuid());

        $this->mockGetByUuid($this->label);

        $actualDomainMessage = $this->enricher->enrich($this->domainMessage);

        $this->assertEquals($expectedDomainMessage, $actualDomainMessage);
    }

    /**
     * @test
     */
    public function it_does_not_enrich_unsupported_messages()
    {
        $this->mockSupports(false);

        $this->enricher->expects($this->never())
            ->method('getLabelUuid');

        $this->labelReadRepository->expects($this->never())
            ->method('getByUuid');

        $actualDomainMessage = $this->enricher->enrich($this->domainMessage);

        $this->assertEquals($this->domainMessage, $actualDomainMessage);
    }

    /**
     * @test
     */
    public function it_does_not_enrich_when_label_can_not_be_found()
    {
        $this->mockSupports(true);

        $this->mockGetLabelUuid(new UUID());

        $this->mockGetByUuid(null);

        $actualDomainMessage = $this->enricher->enrich($this->domainMessage);

        $this->assertEquals($this->domainMessage, $actualDomainMessage);
    }

    /**
     * @param bool $supports
     */
    private function mockSupports($supports)
    {
        $this->enricher->method('supports')
            ->willReturn($supports);
    }

    /**
     * @param UUID $labelUuid
     */
    private function mockGetLabelUuid(UUID $labelUuid)
    {
        $this->enricher->method('getLabelUuid')
            ->willReturn($labelUuid);
    }

    /**
     * @param Entity|null $label
     */
    private function mockGetByUuid(Entity $label = null)
    {
        $this->labelReadRepository->method('getByUuid')
            ->willReturn($label);
    }
}
