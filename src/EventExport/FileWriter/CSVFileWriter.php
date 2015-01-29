<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;


class CSVFileWriter implements FileWriterInterface
{

    protected $f;

    protected $delimiter;

    public function __construct($filePath) {
        $this->f = fopen($filePath, 'w');

        $this->delimiter = ',';

        // Overwrite default Excel delimiter.
        fwrite($this->f, "sep={$this->delimiter}\n");
    }

    /**
     * @param mixed $event
     */
    public function exportEvent($event)
    {
        $event = json_decode($event);

        $data[] = $event->{'@id'};
        $data[] = reset($event->name);
        $data[] = $event->creator;

        fputcsv($this->f, $data, $this->delimiter);
    }

    public function close()
    {
        fclose($this->f);
    }
}
