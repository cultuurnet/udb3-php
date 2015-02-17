<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport;

use Broadway\CommandHandling\CommandHandler;
use CultuurNet\UDB3\EventExport\Command\ExportEventsAsCSV;
use CultuurNet\UDB3\EventExport\Command\ExportEventsAsJsonLD;
use CultuurNet\UDB3\EventExport\Command\ExportEventsAsOOXML;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class EventExportCommandHandler extends CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var EventExportServiceInterface
     */
    protected $eventExportService;

    public function __construct(EventExportServiceInterface $eventExportService)
    {
        $this->eventExportService = $eventExportService;
    }

    public function handleExportEventsAsJsonLD(
        ExportEventsAsJsonLD $exportCommand
    ) {
        $this->eventExportService->exportEventsAsJsonLD(
            $exportCommand->getQuery(),
            $exportCommand->getAddress(),
            $this->logger,
            $exportCommand->getSelection(),
            $exportCommand->getInclude()
        );
    }

    public function handleExportEventsAsCSV(
        ExportEventsAsCSV $exportCommand
    ) {
        $this->eventExportService->exportEventsAsCSV(
            $exportCommand->getQuery(),
            $exportCommand->getAddress(),
            $this->logger,
            $exportCommand->getSelection(),
            $exportCommand->getInclude()
        );

    }

    public function handleExportEventsAsOOXML(
        ExportEventsAsOOXML $exportCommand
    ) {
        $this->eventExportService->exportEventsAsOOXML(
            $exportCommand->getQuery(),
            $exportCommand->getAddress(),
            $this->logger,
            $exportCommand->getSelection(),
            $exportCommand->getInclude()
        );

    }
}
