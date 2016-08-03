<?php

namespace CultuurNet\UDB3\Label;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\DomainMessage\DomainMessageTestDataTrait;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Place\Events\LabelAdded as PlaceLabelAdded;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class OfferLabelDomainMessageEnricherTest extends \PHPUnit_Framework_TestCase
{
    use DomainMessageTestDataTrait;

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readRepository;

    /**
     * @var OfferLabelDomainMessageEnricher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $enricher;

    public function setUp()
    {
        $this->readRepository = $this->getMock(ReadRepositoryInterface::class);
        $this->enricher = new OfferLabelDomainMessageEnricher($this->readRepository);
    }

    /**
     * @test
     */
    public function it_supports_abstract_label_events_on_offer()
    {
        $supported = $this->createDomainMessage($this, AbstractLabelEvent::class);
        $this->assertTrue($this->enricher->supports($supported));

        $supported = $this->createDomainMessage($this, AbstractLabelAdded::class);
        $this->assertTrue($this->enricher->supports($supported));

        $supported = $this->createDomainMessage($this, PlaceLabelAdded::class);
        $this->assertTrue($this->enricher->supports($supported));
    }

    /**
     * @test
     */
    public function it_does_not_support_events_which_are_not_an_instance_of_abstract_label_events()
    {
        $unsupported = $this->createDomainMessage($this, EventCreated::class);
        $this->assertFalse($this->enricher->supports($unsupported));
    }

    /**
     * @test
     */
    public function it_does_not_enrich_unsupported_domain_messages()
    {
        $unsupported = $this->createDomainMessage($this, EventCreated::class);
        $enriched = $this->enricher->enrich($unsupported);

        $this->assertEquals($unsupported, $enriched);
    }

    /**
     * @test
     */
    public function it_enriches_abstract_label_events_if_the_label_can_be_resolved()
    {
        $labelName = new StringLiteral('foo');
        $labelUuid = new UUID('D0A6BFBC-74E5-4314-966C-5D376EF82361');

        $id = '81B8438E-9B2F-4518-9D2C-BCD87247AAA4';

        $playhead = 1;

        $payload = $this->getMockForAbstractClass(
            AbstractLabelEvent::class,
            [
                '81B8438E-9B2F-4518-9D2C-BCD87247AAA4',
                new Label(
                    (string) $labelName,
                    true
                ),
            ]
        );

        $recordedOn = DateTime::now();

        $originalDomainMessage = new DomainMessage(
            $id,
            $playhead,
            new Metadata(
                [
                    'ip' => '127.0.0.1',
                ]
            ),
            $payload,
            $recordedOn
        );

        $labelDetail = new Entity(
            $labelUuid,
            $labelName,
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC()
        );

        $expectedEnrichedDomainMessage = $originalDomainMessage->andMetadata(
            new Metadata(['labelUuid' => (string) $labelUuid])
        );

        $this->readRepository->expects($this->once())
            ->method('getByName')
            ->with($labelName)
            ->willReturn($labelDetail);

        $actualEnrichedDomainMessage = $this->enricher->enrich($originalDomainMessage);

        $this->assertEquals($expectedEnrichedDomainMessage, $actualEnrichedDomainMessage);
    }

    /**
     * @test
     */
    public function it_does_not_enrich_abstract_label_events_if_the_label_can_not_be_resolved()
    {
        $labelName = new StringLiteral('foo');

        $id = '81B8438E-9B2F-4518-9D2C-BCD87247AAA4';

        $playhead = 1;

        $payload = $this->getMockForAbstractClass(
            AbstractLabelEvent::class,
            [
                '81B8438E-9B2F-4518-9D2C-BCD87247AAA4',
                new Label(
                    (string) $labelName,
                    true
                ),
            ]
        );

        $recordedOn = DateTime::now();

        $originalDomainMessage = new DomainMessage(
            $id,
            $playhead,
            new Metadata(
                [
                    'ip' => '127.0.0.1',
                ]
            ),
            $payload,
            $recordedOn
        );

        $this->readRepository->expects($this->once())
            ->method('getByName')
            ->with($labelName)
            ->willReturn(null);

        $enrichedDomainMessage = $this->enricher->enrich($originalDomainMessage);

        $this->assertEquals($originalDomainMessage, $enrichedDomainMessage);
    }
}
