<?php

namespace CultuurNet\UDB3\Offer\Commands\Moderation;

use ValueObjects\Identity\UUID;

class AbstractPublishTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DateTimeInterface
     */
    private $publicationDate;

    /**
     * @var AbstractPublish|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractPublish;

    public function setUp()
    {
        $this->publicationDate = new \DateTime();

        $this->abstractPublish = $this->getMockForAbstractClass(
            AbstractPublish::class,
            [new UUID(), $this->publicationDate]
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
    public function it_can_store_an_publication_date()
    {
        $this->assertEquals(
            $this->publicationDate,
            $this->abstractPublish->getPublicationDate()
        );
    }

    /**
     * @test
     */
    public function it_has_a_default_publication_date_of_now()
    {
        $now = new \DateTime();

        $abstractPublish = $this->getMockForAbstractClass(
            AbstractPublish::class,
            [new UUID()]
        );

        $this->assertEquals($now, $abstractPublish->getPublicationDate());
    }
}
