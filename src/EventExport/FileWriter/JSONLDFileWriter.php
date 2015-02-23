<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;

class JSONLDFileWriter implements FileWriterInterface
{
    protected $f;

    /**
     * @var JSONLDEventFormatter
     */
    protected $eventFormatter;

    public function __construct($filePath, $include = null)
    {
        $this->f = fopen($filePath, 'w');
        if (false === $this->f) {
            throw new \RuntimeException(
                'Unable to open file for writing: ' . $filePath
            );
        }
        fwrite($this->f, '[');

        $this->eventFormatter = new JSONLDEventFormatter($include);

        $this->first = true;
    }

    /**
     * @param mixed $event
     */
    public function exportEvent($event)
    {
        if ($this->first) {
            $this->first = false;
        } else {
            fwrite($this->f, ',');
        }

        $formattedEvent = $this->eventFormatter->formatEvent($event);

        fwrite($this->f, $formattedEvent);
    }

    public function close()
    {
        if (is_resource($this->f)) {
            fwrite($this->f, ']');

            fclose($this->f);
        }
    }
}
