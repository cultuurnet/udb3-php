<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\EventExport\Notification\NotificationMailerInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use PHPUnit_Framework_TestCase;

class EventExportServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventExportService
     */
    protected $eventExportService;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var SearchServiceInterface
     */
    protected $searchService;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var string
     */
    protected $publicDirectory;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @var NotificationMailerInterface
     */
    protected $mailer;

    public function setUp()
    {
        $this->eventService = $this->getMock(EventServiceInterface::class);
        $this->searchService = $this->getMock(SearchServiceInterface::class);
        $this->uuidGenerator = $this->getMock(UuidGeneratorInterface::class);
        $this->publicDirectory = 'foo';
        $this->iriGenerator = $this->getMock(IriGeneratorInterface::class);
        $this->mailer = $this->getMock(NotificationMailerInterface::class);

        $this->eventExportService = new EventExportService(
            $this->eventService,
            $this->searchService,
            $this->uuidGenerator,
            $this->publicDirectory,
            $this->iriGenerator,
            $this->mailer
        );
    }

    /**
     * @test
     */
    public function it_exports_events_to_a_file()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function it_sends_an_email_with_a_link_to_the_export_if_address_is_provided()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function it_ignores_items_that_can_not_be_found_by_the_event_service()
    {
        $this->markTestIncomplete();
    }
}
