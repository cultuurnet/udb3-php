<?php

namespace CultuurNet\UDB3\DomainMessage;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Place\Events\PlaceCreated;

class CompositeDomainMessageEnricherTest extends \PHPUnit_Framework_TestCase
{
    use DomainMessageTestDataTrait;

    /**
     * @var DomainMessageEnricherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventCreatedEnricher;

    /**
     * @var DomainMessageEnricherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeCreatedEnricher;

    /**
     * @var CompositeDomainMessageEnricher
     */
    private $compositeEnricher;

    public function setUp()
    {
        $this->eventCreatedEnricher = $this->createMock(DomainMessageEnricherInterface::class);
        $this->eventCreatedEnricher->expects($this->any())
            ->method('supports')
            ->willReturnCallback(
                function (DomainMessage $domainMessage) {
                    $payload = $domainMessage->getPayload();
                    return $payload instanceof EventCreated;
                }
            );

        $this->placeCreatedEnricher = $this->createMock(DomainMessageEnricherInterface::class);
        $this->placeCreatedEnricher->expects($this->any())
            ->method('supports')
            ->willReturnCallback(
                function (DomainMessage $domainMessage) {
                    $payload = $domainMessage->getPayload();
                    return $payload instanceof PlaceCreated;
                }
            );

        $this->compositeEnricher = (new CompositeDomainMessageEnricher())
            ->withEnricher($this->eventCreatedEnricher)
            ->withEnricher($this->placeCreatedEnricher);
    }

    /**
     * @test
     */
    public function it_only_supports_domain_messages_supported_by_its_injected_enrichers()
    {
        $eventCreatedDomainMessage = $this->createDomainMessage($this, EventCreated::class);
        $placeCreatedDomainMessage = $this->createDomainMessage($this, PlaceCreated::class);
        $organizerCreatedDomainMessage = $this->createDomainMessage($this, OrganizerCreated::class);
        $organizerCreatedWithUniqueWebsiteDomainMessage = $this->createDomainMessage(
            $this,
            OrganizerCreatedWithUniqueWebsite::class
        );

        $this->assertTrue($this->compositeEnricher->supports($eventCreatedDomainMessage));
        $this->assertTrue($this->compositeEnricher->supports($placeCreatedDomainMessage));
        $this->assertFalse($this->compositeEnricher->supports($organizerCreatedDomainMessage));
        $this->assertFalse($this->compositeEnricher->supports($organizerCreatedWithUniqueWebsiteDomainMessage));
    }

    /**
     * @test
     */
    public function it_delegates_enrichment_of_supported_domain_messages()
    {
        $eventCreatedDomainMessage = $this->createDomainMessage($this, EventCreated::class);
        $placeCreatedDomainMessage = $this->createDomainMessage($this, PlaceCreated::class);
        $organizerCreatedDomainMessage = $this->createDomainMessage($this, OrganizerCreated::class);
        $organizerCreatedWithUniqueWebsiteDomainMessage = $this->createDomainMessage(
            $this,
            OrganizerCreatedWithUniqueWebsite::class
        );


        $enrichedEventCreatedDomainMessage = clone $eventCreatedDomainMessage;
        $enrichedEventCreatedDomainMessage->extraProperty = true;

        $enrichedPlaceCreatedDomainMessage = clone $placeCreatedDomainMessage;
        $enrichedPlaceCreatedDomainMessage->extraProperty = true;

        $this->eventCreatedEnricher->expects($this->once())
            ->method('enrich')
            ->with($eventCreatedDomainMessage)
            ->willReturn($enrichedEventCreatedDomainMessage);

        $this->placeCreatedEnricher->expects($this->once())
            ->method('enrich')
            ->with($placeCreatedDomainMessage)
            ->willReturn($enrichedPlaceCreatedDomainMessage);

        $this->assertEquals(
            $enrichedEventCreatedDomainMessage,
            $this->compositeEnricher->enrich($eventCreatedDomainMessage)
        );

        $this->assertEquals(
            $enrichedPlaceCreatedDomainMessage,
            $this->compositeEnricher->enrich($placeCreatedDomainMessage)
        );

        $this->assertEquals(
            $organizerCreatedDomainMessage,
            $this->compositeEnricher->enrich($organizerCreatedDomainMessage)
        );

        $this->assertEquals(
            $organizerCreatedDomainMessage,
            $this->compositeEnricher->enrich($organizerCreatedDomainMessage)
        );

        $this->assertEquals(
            $organizerCreatedWithUniqueWebsiteDomainMessage,
            $this->compositeEnricher->enrich($organizerCreatedWithUniqueWebsiteDomainMessage)
        );
    }
}
