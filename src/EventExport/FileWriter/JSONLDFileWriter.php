<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;


class JSONLDFileWriter implements FileWriterInterface
{
    protected $f;

    /**
     * @var string[]
     */
    protected $includedProperties;

    public function __construct($filePath, $include = null) {
        $this->f = fopen($filePath, 'w');
        if (false === $this->f) {
            throw new \RuntimeException('Unable to open file for writing: ' . $filePath);
        }
        fwrite($this->f, '[');

        $this->first = true;

        if($include) {
            $include[] = '@id';
            // The address property is nested inside location.
            // The whole location property gets included instead of pulling it
            // out and placing it directly on the object.
            if(in_array('address', $include) && !in_array('location', $include)) {
                array_push($include, 'location');
            }
            $this->includedProperties = $include;
        }

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

        if($this->includedProperties) {
            $eventObject = json_decode($event);
            foreach($eventObject as $propertyName => $value) {
                var_dump($propertyName);
                if(!in_array($propertyName, $this->includedProperties)) {
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
