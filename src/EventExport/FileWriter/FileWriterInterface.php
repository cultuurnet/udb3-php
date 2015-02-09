<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;


interface FileWriterInterface {

    /**
     * @param mixed $event
     * @param string[] $include
     * @return void
     */
    public function exportEvent($event, $include);

    /**
     * @return void
     */
    public function close();
}
