<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;

class JSONLDFileWriter implements FileWriterInterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var JSONLDEventFormatter
     */
    protected $eventFormatter;

    public function __construct($filePath, $include = null)
    {
        $this->filePath = $filePath;
        $this->eventFormatter = new JSONLDEventFormatter($include);
    }

    /**
     * {@inheritdoc}
     */
    public function write($events)
    {
        $file = fopen($this->filePath, 'w');
        if (false === $file) {
            throw new \RuntimeException(
                'Unable to open file for writing: ' . $this->filePath
            );
        }
        fwrite($file, '[');

        $first = true;
        foreach ($events as $event) {
            if ($first) {
                $first = false;
            } else {
                fwrite($file, ',');
            }

            $formattedEvent = $this->eventFormatter->formatEvent($event);

            fwrite($file, $formattedEvent);
        }

        fwrite($file, ']');
        fclose($file);
    }
}
