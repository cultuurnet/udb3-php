<?php

namespace CultuurNet\UDB3\EventExport\Command;

use CultuurNet\UDB3\EventExport\EventExportQuery;
use CultuurNet\UDB3\EventExport\HTML\Brand;
use CultuurNet\UDB3\EventExport\HTML\Title;
use ValueObjects\Web\EmailAddress;

class ExportEventsAsPDFTest extends \PHPUnit_Framework_TestCase
{

    private $export;

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

        $this->assertNotSame($newExport, $this->export);
        $this->assertEquals($email, $newExport->getAddress());

        $this->assertEquals($this->clonedExport, $this->export);
    }
}
