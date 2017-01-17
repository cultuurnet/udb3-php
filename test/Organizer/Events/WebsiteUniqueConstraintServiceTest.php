<?php

namespace CultuurNet\UDB3\Organizer\Events;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Title;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class WebsiteUniqueConstraintServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebsiteUniqueConstraintService
     */
    private $service;

    /**
     * @var string
     */
    private $organizerId;

    /**
     * @var DomainMessage
     */
    private $supportedEvent;

    /**
     * @var DomainMessage
     */
    private $unsupportedEvent;

    /**
     * @var StringLiteral
     */
    private $uniqueConstraintValue;

    public function setUp()
    {
        $this->service = new WebsiteUniqueConstraintService();

        $this->organizerId = '2fad63f2-4da2-4c32-ae97-6a581d0e84d2';

        $this->supportedEvent = DomainMessage::recordNow(
            $this->organizerId,
            0,
            new Metadata([]),
            new OrganizerCreatedWithUniqueWebsite(
                $this->organizerId,
                Url::fromNative('http://cultuurnet.be'),
                new Title('CultuurNet')
            )
        );

        $this->unsupportedEvent = DomainMessage::recordNow(
            $this->organizerId,
            0,
            new Metadata([]),
            new OrganizerCreated(
                $this->organizerId,
                new Title('CultuurNet'),
                [],
                [],
                [],
                []
            )
        );

        $this->uniqueConstraintValue = new StringLiteral('http://cultuurnet.be');
    }

    /**
     * @test
     */
    public function it_supports_organizer_created_with_unique_website_events()
    {
        $this->assertTrue($this->service->hasUniqueConstraint($this->supportedEvent));
    }

    /**
     * @test
     */
    public function it_does_not_support_organizer_created_events()
    {
        $this->assertFalse($this->service->hasUniqueConstraint($this->unsupportedEvent));
    }

    /**
     * @test
     */
    public function it_returns_the_unique_constraint_value_from_supported_events()
    {
        $this->assertEquals(
            $this->uniqueConstraintValue,
            $this->service->getUniqueConstraintValue($this->supportedEvent)
        );
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_trying_to_get_a_unique_constraint_value_from_unsupported_events()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->service->getUniqueConstraintValue($this->unsupportedEvent);
    }
}
