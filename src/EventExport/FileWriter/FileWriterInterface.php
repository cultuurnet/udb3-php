<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;


interface FileWriterInterface {

    /**
     * @param mixed $event
     * @return void
     */
    public function exportEvent($event);

    /**
     * @return void
     */
    public function close();
}
