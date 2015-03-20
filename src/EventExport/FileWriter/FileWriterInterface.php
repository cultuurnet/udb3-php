<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;

interface FileWriterInterface
{
    /**
     * @param \Traversable $events
     * @return void
     */
    public function write($events);
}
