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
        // UTF-16LE BOM
        fwrite($this->f, "\xFF\xFE");
        fwrite($this->f, "sep={$this->delimiter}");
        fwrite($this->f, PHP_EOL);

        $this->writeCSV($this->columns());
    }

    protected function writeCSV($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = iconv('UTF-8', 'UTF-16LE//IGNORE', $value);
        }

        fputcsv($this->f, $data, $this->delimiter);
    }

    /**
     * @param mixed $event
     */
    public function exportEvent($event)
    {
        $event = json_decode($event);

        $row = $this->emptyRow();
        $row['id'] = $event->{'@id'};
        $row['titel'] = reset($event->name);
        $row['invoerder'] = $event->creator;
        if (isset($event->price)) {
            $row['prijs'] = $event->price;
        }
        $row['omschrijving'] = reset($event->description);

        $this->writeCSV($row);
    }

    public function emptyRow() {
        return array_fill_keys($this->columns(), '');
    }

    public function columns() {
        return [
            'id',
            'titel',
            'invoerder',
            'prijs',
            'omschrijving',
        ];
    }

    public function close()
    {
        fclose($this->f);
    }
}
