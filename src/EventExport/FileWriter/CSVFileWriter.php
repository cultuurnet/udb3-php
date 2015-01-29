<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;


class CSVFileWriter implements FileWriterInterface
{

    protected $f;

    public function __construct($filePath) {
        $this->f = fopen($filePath, 'w');
    }

    /**
     * @param mixed $event
     */
    public function exportEvent($event)
    {
        fputcsv($this->f, (array)$event);
    }

    public function close()
    {
        fclose($this->f);
    }
}
