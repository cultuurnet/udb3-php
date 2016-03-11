<?php

namespace CultuurNet\UDB3\Variations\Model\Properties;

use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\Command\ValidationException;

class DefaultUrlValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IriOfferIdentifierFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iriOfferIdentifierFactory;

    /**
     * @var EntityServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventService;

    /**
     * @var EntityServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeService;

    /**
     * @var DefaultUrlValidator
     */
    private $defaultUrlValidator;

    public function setUp()
    {
        $this->iriOfferIdentifierFactory = $this->getMock(IriOfferIdentifierFactoryInterface::class);
        $this->eventService = $this->getMock(EntityServiceInterface::class);
        $this->placeService = $this->getMock(EntityServiceInterface::class);

        $this->defaultUrlValidator = new DefaultUrlValidator(
            $this->iriOfferIdentifierFactory
        );

        $this->defaultUrlValidator = $this->defaultUrlValidator->withEntityService(
            OfferType::EVENT(),
            $this->eventService
        );
        $this->defaultUrlValidator = $this->defaultUrlValidator->withEntityService(
            OfferType::PLACE(),
            $this->placeService
        );
    }

    /**
     * @test
     */
    public function it_tries_to_get_the_event_when_an_event_url_is_given()
    {
        $this->eventService->expects($this->once())
            ->method('getEntity')
            ->with('foo-bar');

        $this->placeService->expects($this->never())
            ->method('getEntity');

        $identifier = new IriOfferIdentifier(
            'https://foo.bar/event/foo-bar',
            'foo-bar',
            OfferType::EVENT()
        );

        $this->iriOfferIdentifierFactory->expects($this->once())
            ->method('fromIri')
            ->with('https://foo.bar/event/foo-bar')
            ->willReturn($identifier);

        $this->defaultUrlValidator->validateUrl(
            new Url('https://foo.bar/event/foo-bar')
        );
    }

    /**
     * @test
     */
    public function it_tries_to_get_the_place_when_a_place_url_is_given()
    {
        $this->placeService->expects($this->once())
            ->method('getEntity')
            ->with('foo-bar-place');

        $this->eventService->expects($this->never())
            ->method('getEntity');

        $identifier = new IriOfferIdentifier(
            'https://foo.bar/place/foo-bar-place',
            'foo-bar-place',
            OfferType::PLACE()
        );

        $this->iriOfferIdentifierFactory->expects($this->once())
            ->method('fromIri')
            ->with('https://foo-bar/place/foo-bar-place')
            ->willReturn($identifier);

        $this->defaultUrlValidator->validateUrl(
            new Url('https://foo-bar/place/foo-bar-place')
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_event_cannot_be_found()
    {
        $this->eventService->expects($this->once())
            ->method('getEntity')
            ->with('foo-bar')
            ->willThrowException(new EntityNotFoundException());

        $identifier = new IriOfferIdentifier(
            'https://foo.bar/event/foo-bar',
            'foo-bar',
            OfferType::EVENT()
        );

        $this->iriOfferIdentifierFactory->expects($this->once())
            ->method('fromIri')
            ->with('https://foo-bar/event/foo-bar')
            ->willReturn($identifier);

        $this->setExpectedException(
            ValidationException::class,
            'Invalid data.'
        );

        $this->defaultUrlValidator->validateUrl(
            new Url('https://foo-bar/event/foo-bar')
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_no_entity_service_can_be_found_for_a_certain_offer_type()
    {
        $defaultUrlValidator = new DefaultUrlValidator(
            $this->iriOfferIdentifierFactory
        );

        $defaultUrlValidator = $defaultUrlValidator->withEntityService(
            OfferType::EVENT(),
            $this->eventService
        );

        $identifier = new IriOfferIdentifier(
            'https://foo.bar/place/foo-bar-place',
            'foo-bar-place',
            OfferType::PLACE()
        );

        $this->iriOfferIdentifierFactory->expects($this->once())
            ->method('fromIri')
            ->with('https://foo-bar/place/foo-bar-place')
            ->willReturn($identifier);

        $this->setExpectedException(
            \LogicException::class,
            'Found no repository for type Place.'
        );

        $defaultUrlValidator->validateUrl(
            new Url('https://foo-bar/place/foo-bar-place')
        );
    }
}
