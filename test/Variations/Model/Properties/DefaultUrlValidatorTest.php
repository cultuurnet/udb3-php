<?php

namespace CultuurNet\UDB3\Variations\Model\Properties;

use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Variations\Command\ValidationException;

class DefaultUrlValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IriOfferIdentifierFactoryInterface
     */
    private $iriOfferIdentifierFactory;

    /**
     * @var EntityServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityService;

    /**
     * @var DefaultUrlValidator
     */
    private $defaultUrlValidator;

    public function setUp()
    {
        $this->iriOfferIdentifierFactory = $this->getMock(IriOfferIdentifierFactoryInterface::class);
        $this->entityService = $this->getMock(EntityServiceInterface::class);
        $this->defaultUrlValidator(
            $this->iriOfferIdentifierFactory
        );
    }

    /**
     * @test
     */
    public function it_tries_to_get_the_event_when_an_event_url_is_given()
    {
        $defaultUrlValidator = new DefaultUrlValidator(
            $this->regExpPattern,
            $this->eventService,
            $this->placeService
        );

        $this->eventService->expects($this->once())
            ->method('getEvent')
            ->with('foo-bar');

        $defaultUrlValidator->validateUrl(
            new Url('https://culudb-silex.dev:8080/event/foo-bar')
        );
    }

    /**
     * @test
     */
    public function it_tries_to_get_the_place_when_a_place_url_is_given()
    {
        $defaultUrlValidator = new DefaultUrlValidator(
            $this->regExpPattern,
            $this->eventService,
            $this->placeService
        );

        $this->placeService->expects($this->once())
            ->method('getEntity')
            ->with('foo-bar');

        $defaultUrlValidator->validateUrl(
            new Url('https://culudb-silex.dev:8080/place/foo-bar')
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_event_cannot_be_found()
    {
        $defaultUrlValidator = new DefaultUrlValidator(
            $this->regExpPattern,
            $this->eventService,
            $this->placeService
        );

        $this->eventService->expects($this->once())
            ->method('getEvent')
            ->with('foo-bar')
            ->willThrowException(new EventNotFoundException());

        $this->setExpectedException(
            ValidationException::class,
            'Invalid data.'
        );

        $defaultUrlValidator->validateUrl(
            new Url('https://culudb-silex.dev:8080/event/foo-bar')
        );
    }
}
