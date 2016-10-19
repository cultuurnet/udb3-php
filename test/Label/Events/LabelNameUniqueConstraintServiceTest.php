<?php

namespace CultuurNet\UDB3\Label\Events;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class LabelNameUniqueConstraintServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * @var DomainMessage
     */
    private $created;

    /**
     * @var DomainMessage
     */
    private $copyCreated;

    /**
     * @var LabelNameUniqueConstraintService
     */
    private $uniqueHelper;

    protected function setUp()
    {
        $this->name = new StringLiteral('labelName');

        $this->created = $this->createDomainMessage(new Created(
            new UUID(),
            $this->name,
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PRIVATE()
        ));

        $this->copyCreated = $this->createDomainMessage(new CopyCreated(
            new UUID(),
            $this->name,
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PRIVATE(),
            new UUID()
        ));

        $this->uniqueHelper = new LabelNameUniqueConstraintService();
    }

    /**
     * @test
     */
    public function it_requires_unique_for_created()
    {
        $this->assertTrue($this->uniqueHelper->hasUniqueConstraint($this->created));
    }

    /**
     * @test
     */
    public function it_requires_unique_for_copy_created()
    {
        $this->assertTrue($this->uniqueHelper->hasUniqueConstraint(
            $this->copyCreated
        ));
    }

    /**
     * @test
     */
    public function it_does_not_require_unique_for_made_invisible()
    {
        $this->assertFalse($this->uniqueHelper->hasUniqueConstraint(
            $this->createDomainMessage(new MadeInvisible(new UUID()))
        ));
    }

    /**
     * @test
     */
    public function it_can_get_unique_from_created()
    {
        $this->assertEquals(
            $this->name,
            $this->uniqueHelper->getUniqueConstraintValue($this->created)
        );
    }

    /**
     * @test
     */
    public function it_can_get_unique_from_copy_created()
    {
        $this->assertEquals(
            $this->name,
            $this->uniqueHelper->getUniqueConstraintValue($this->copyCreated)
        );
    }

    /**
     * @param AbstractEvent $event
     * @return DomainMessage
     */
    private function createDomainMessage(AbstractEvent $event)
    {
        return new DomainMessage(
            $event->getUuid(),
            0,
            new Metadata(),
            $event,
            BroadwayDateTime::now()
        );
    }
}