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
        if (false === $this->f) {
            throw new \RuntimeException('Unable to open file for writing: ' . $filePath);
        }
        fwrite($this->f, '[');

        $this->first = true;
    }

    /**
     * @param mixed $event
     * @param string[] $include
     */
    public function exportEvent($event, $include)
    {
        if ($this->first) {
            $this->first = false;
        }
        else {
            fwrite($this->f, ',');
        }

        if($include) {
            $include[] = '@id';
            $eventObject = json_decode($event);
            foreach($eventObject as $propertyName => $value) {
                var_dump($propertyName);
                if(!in_array($propertyName, $include)) {
                    unset($eventObject->{$propertyName});
                }
            }
            $event = json_encode($eventObject);
        }

        fwrite($this->f, $event);
    }

    public function close() {
        if (is_resource($this->f)) {
            fwrite($this->f, ']');

            fclose($this->f);
        }
    }
}
