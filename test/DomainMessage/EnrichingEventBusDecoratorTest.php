<?php

namespace CultuurNet\UDB3\DomainMessage;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\EventBus\DomainMessageTestDataTrait;
use CultuurNet\UDB3\EventBus\EnrichingEventBusDecorator;
use CultuurNet\UDB3\Place\Events\PlaceCreated;

class EnrichingEventBusDecoratorTest extends \PHPUnit_Framework_TestCase
{
    use DomainMessageTestDataTrait;

    /**
     * @var DomainMessageEnricherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $enricher;

    /**
     * @var EventBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratee;

    /**
     * @var EnrichingEventBusDecorator
     */
    private $enrichingDecorator;

    public function setUp()
    {
        $this->enricher = $this->getMock(DomainMessageEnricherInterface::class);
        $this->decoratee = $this->getMock(EventBusInterface::class);
        $this->enrichingDecorator = new EnrichingEventBusDecorator($this->decoratee, $this->enricher);
    }

    /**
     * @test
     */
    public function it_delegates_subscriptions_to_the_decoratee()
    {
        /* @var EventListenerInterface $subscriber */
        $subscriber = $this->getMock(EventListenerInterface::class);

        $this->decoratee->expects($this->once())
            ->method('subscribe')
            ->with($subscriber);

        $this->enrichingDecorator->subscribe($subscriber);
    }

    /**
     * @test
     */
    public function it_enriches_supported_domain_messages_before_delegating_them_to_the_decoratee()
    {
        $supportedDomainMessage = $this->createDomainMessage($this, EventCreated::class);
        $otherDomainMessage = $this->createDomainMessage($this, PlaceCreated::class);

        $stream = new DomainEventStream(
            [
                $supportedDomainMessage,
                $otherDomainMessage,
            ]
        );

        $enrichedDomainMessage = clone $supportedDomainMessage;
        $enrichedDomainMessage->extraProperty = true;

        $enrichedStream = new DomainEventStream(
            [
                $enrichedDomainMessage,
                $otherDomainMessage,
            ]
        );

        $this->enricher->expects($this->any())
            ->method('supports')
            ->willReturnCallback(
                function (DomainMessage $domainMessage) use ($supportedDomainMessage) {
                    return $domainMessage == $supportedDomainMessage;
                }
            );

        $this->enricher->expects($this->once())
            ->method('enrich')
            ->with($supportedDomainMessage)
            ->willReturn($enrichedDomainMessage);

        $this->decoratee->expects($this->once())
            ->method('publish')
            ->with($enrichedStream);

        $this->enrichingDecorator->publish($stream);
    }
}
