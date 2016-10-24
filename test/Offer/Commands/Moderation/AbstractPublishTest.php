<?php

namespace CultuurNet\UDB3\Offer\Commands\Moderation;

use ValueObjects\Identity\UUID;

class AbstractPublishTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DateTimeInterface
     */
    private $embargoDate;

    /**
     * @var AbstractPublish|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractPublish;

    public function setUp()
    {
        $this->embargoDate = new \DateTime();

        $this->abstractPublish = $this->getMockForAbstractClass(
            AbstractPublish::class,
            [new UUID(), $this->embargoDate]
        );
    }

    /**
     * @test
     */
    public function it_is_an_abstract_moderation_command()
    {
        $this->assertTrue(is_subclass_of(
            $this->abstractPublish,
            AbstractModerationCommand::class
        ));
    }

    /**
     * @test
     */
    public function it_can_store_an_embargo_date()
    {
        $this->assertEquals(
            $this->embargoDate,
            $this->abstractPublish->getEmbargoDate()
        );
    }

    /**
     * @test
     */
    public function it_has_a_default_embargo_date_of_null()
    {
        $abstractPublish = $this->getMockForAbstractClass(
            AbstractPublish::class,
            [new UUID()]
        );

        $this->assertEquals(
            null,
            $abstractPublish->getEmbargoDate()
        );
    }
}
