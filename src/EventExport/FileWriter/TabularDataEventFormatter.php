<?php

namespace CultuurNet\UDB3\EventExport\FileWriter;

class TabularDataEventFormatter
{
    /**
     * @var string[]
     */
    protected $includedProperties;

    protected $columns;

    public function __construct($columns, $include)
    {
        $this->includedProperties = $include;
        $this->columns = $columns;
    }

    public function formatEvent($event)
    {
        $event = json_decode($event);
        $includedProperties = $this->includedProperties;
        $row = $this->emptyRow();

        foreach ($includedProperties as $property) {
            $column = $this->columns[$property];
            $value = $column['include']($event);

            if ($value) {
                $row[$property] = $value;
            } else {
                $row[$property] = '';
            }
        }

        return $row;
    }

    public function emptyRow()
    {
        $row = array();

        foreach ($this->includedProperties as $property) {
            $row[$property] = '';
        }

        return $row;
    }
}
