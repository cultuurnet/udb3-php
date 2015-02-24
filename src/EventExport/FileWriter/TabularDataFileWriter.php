<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;

class TabularDataFileWriter implements FileWriterInterface
{
    /**
     * @var string[]
     */
    protected $includedProperties;

    /**
     * @var TabularDataEventFormatter
     */
    protected $eventFormatter;

    /**
     * @var TabularDataFileWriterInterface
     */
    protected $tabularDataFileWriter;

    public function __construct(
        TabularDataFileWriterInterface $tabularDataFileWriter,
        $include
    ) {
        $this->tabularDataFileWriter = $tabularDataFileWriter;
        $this->eventFormatter = new TabularDataEventFormatter($include);
        $this->writeHeader();
    }

    protected function writeHeader()
    {
        $headerRow = $this->eventFormatter->formatHeader();

        $this->tabularDataFileWriter->writeRow($headerRow);
    }

    /**
     * @param mixed $event
     */
    public function exportEvent($event)
    {
        $eventRow = $this->eventFormatter->formatEvent($event);

        $this->tabularDataFileWriter->writeRow($eventRow);
    }

    /**
     * @return void
     */
    public function close()
    {
        $this->tabularDataFileWriter->close();
    }
}
