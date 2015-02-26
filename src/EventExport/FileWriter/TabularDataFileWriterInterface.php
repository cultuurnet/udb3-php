<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;


interface TabularDataFileWriterInterface
{
    /**
     * @param string[] $row
     */
    public function writeRow($row);

    public function close();
}
