<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;


class JSONLDFileWriter implements FileWriterInterface
{
    protected $f;

    public function __construct($filePath) {
        $this->f = fopen($filePath, 'w');
        fwrite($this->f, '[');

        $this->first = true;
    }

    /**
     * @param mixed $event
     */
    public function exportEvent($event)
    {
        if ($this->first) {
            $this->first = false;
        }
        else {
            fwrite($this->f, ',');
        }

        fwrite($this->f, $event);
    }

    public function close() {
        fwrite($this->f, ']');

        fclose($this->f);
    }
}
