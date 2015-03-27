<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport;

use Broadway\CommandHandling\CommandHandler;
use CultuurNet\UDB3\EventExport\Command\ExportEventsAsCSV;
use CultuurNet\UDB3\EventExport\Command\ExportEventsAsJsonLD;
use CultuurNet\UDB3\EventExport\Command\ExportEventsAsOOXML;
use CultuurNet\UDB3\EventExport\Command\ExportEventsAsPDF;
use CultuurNet\UDB3\EventExport\Format\HTML\PDFWebArchiveFileFormat;
use CultuurNet\UDB3\EventExport\Format\HTML\ZippedWebArchiveFileFormat;
use CultuurNet\UDB3\EventExport\Format\TabularData\CSV\CSVFileFormat;
use CultuurNet\UDB3\EventExport\Format\JSONLD\JSONLDFileFormat;
use CultuurNet\UDB3\EventExport\Format\TabularData\OOXML\OOXMLFileFormat;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class EventExportCommandHandler extends CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var EventExportServiceInterface
     */
    protected $eventExportService;

    /**
     * @var string
     */
    protected $princeXMLBinaryPath;

    /**
     * @param EventExportServiceInterface $eventExportService
     * @param string $princeXMLBinaryPath
     */
    public function __construct(EventExportServiceInterface $eventExportService, $princeXMLBinaryPath)
    {
        $this->eventExportService = $eventExportService;
        $this->princeXMLBinaryPath = $princeXMLBinaryPath;
    }

    public function handleExportEventsAsJsonLD(
        ExportEventsAsJsonLD $exportCommand
    ) {
        $this->eventExportService->exportEvents(
            new JSONLDFileFormat($exportCommand->getInclude()),
            $exportCommand->getQuery(),
            $exportCommand->getAddress(),
            $this->logger,
            $exportCommand->getSelection()
        );
    }

    public function handleExportEventsAsCSV(
        ExportEventsAsCSV $exportCommand
    ) {
        $this->eventExportService->exportEvents(
            new CSVFileFormat($exportCommand->getInclude()),
            $exportCommand->getQuery(),
            $exportCommand->getAddress(),
            $this->logger,
            $exportCommand->getSelection()
        );
    }

    public function handleExportEventsAsOOXML(
        ExportEventsAsOOXML $exportCommand
    ) {
        $this->eventExportService->exportEvents(
            new OOXMLFileFormat($exportCommand->getInclude()),
            $exportCommand->getQuery(),
            $exportCommand->getAddress(),
            $this->logger,
            $exportCommand->getSelection()
        );
    }

    public function handleExportEventsAsPDF(
        ExportEventsAsPDF $exportEvents
    ) {
        $fileFormat = new PDFWebArchiveFileFormat(
            $this->princeXMLBinaryPath,
            $exportEvents->getBrand(),
            $exportEvents->getTitle(),
            $exportEvents->getSubtitle(),
            $exportEvents->getFooter(),
            $exportEvents->getPublisher()
        );

        $this->eventExportService->exportEvents(
            $fileFormat,
            $exportEvents->getQuery(),
            $exportEvents->getAddress(),
            $this->logger,
            $exportEvents->getSelection()
        );
    }
}
