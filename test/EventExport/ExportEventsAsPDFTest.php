<?php

namespace CultuurNet\UDB3\EventExport\Command;

use CultuurNet\UDB3\EventExport\EventExportQuery;
use CultuurNet\UDB3\EventExport\HTML\Brand;
use CultuurNet\UDB3\EventExport\HTML\Footer;
use CultuurNet\UDB3\EventExport\HTML\Publisher;
use CultuurNet\UDB3\EventExport\HTML\Subtitle;
use CultuurNet\UDB3\EventExport\HTML\Title;
use ValueObjects\Web\EmailAddress;

class ExportEventsAsPDFTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ExportEventsAsPDF
     */
    private $export;

    /**
     * @var ExportEventsAsPDF
     */
    private $clonedExport;

    public function setUp()
    {
        $this->export = new ExportEventsAsPDF(
            new EventExportQuery('*.*'),
            new Brand('vlieg'),
            new Title('title')
        );
        $this->clonedExport = clone $this->export;
    }

    /**
     * @test
     */
    public function it_allows_to_specify_a_notification_email_address()
    {
        $email = new EmailAddress('john@doe.com');
        $newExport = $this->export->withEmailNotificationTo($email);

        $this->assertEquals($email, $newExport->getAddress());

        $this->assertNotModified($newExport);
    }

    public function it_allows_to_specify_a_subtitle()
    {
        $subtitle = new Subtitle('Some subtitle');
        $newExport = $this->export->withSubtitle($subtitle);

        $this->assertEquals($subtitle, $newExport->getSubtitle());

        $this->assertNotModified($newExport);
    }

    public function it_allows_to_specify_a_footer()
    {
        $footer = new Footer('footer text');
        $newExport = $this->export->withFooter($footer);

        $this->assertEquals($footer, $newExport->getFooter());

        $this->assertNotModified($newExport);
    }

    public function it_allows_to_specify_a_publisher()
    {
        $publisher = new Publisher('footer text');
        $newExport = $this->export->withPublisher($publisher);

        $this->assertEquals($publisher, $newExport->getFooter());

        $this->assertNotModified($newExport);
    }


    private function assertNotModified($newExport) {
        $this->assertNotSame($newExport, $this->export);
        $this->assertEquals($this->clonedExport, $this->export);
    }
}
