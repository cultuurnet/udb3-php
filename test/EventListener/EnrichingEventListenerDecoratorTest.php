<?php

namespace CultuurNet\UDB3\EventListener;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\DomainMessage\DomainMessageEnricherInterface;
use CultuurNet\UDB3\DomainMessage\DomainMessageTestDataTrait;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;

class EnrichingEventListenerDecoratorTest extends \PHPUnit_Framework_TestCase
{
    use DomainMessageTestDataTrait;

    /**
     * @var DomainMessageEnricherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $enricher;

    /**
     * @var EventListenerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratee;

    /**
     * @var EnrichingEventListenerDecorator
     */
    private $enrichingDecorator;

    public function setUp()
    {
        $this->enricher = $this->getMock(DomainMessageEnricherInterface::class);
        $this->decoratee = $this->getMock(EventListenerInterface::class);
        $this->enrichingDecorator = new EnrichingEventListenerDecorator($this->decoratee, $this->enricher);
    }

    /**
     * @test
     */
    public function it_enriches_supported_domain_messages_before_delegating_them_to_the_decoratee()
    {
        $supportedDomainMessage = $this->createDomainMessage($this, EventCreated::class);
        $otherDomainMessage = $this->createDomainMessage($this, PlaceCreated::class);

        $enrichedDomainMessage = clone $supportedDomainMessage;
        $enrichedDomainMessage->extraProperty = true;

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

        $this->decoratee->expects($this->exactly(2))
            ->method('handle')
            ->withConsecutive(
                [$enrichedDomainMessage],
                [$otherDomainMessage]
            );

        $this->enrichingDecorator->handle($supportedDomainMessage);
        $this->enrichingDecorator->handle($otherDomainMessage);
    }
}
