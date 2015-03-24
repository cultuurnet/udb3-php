<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;

interface TabularDataFileWriterFactoryInterface
{
    /**
     * @return TabularDataFileWriterInterface
     * @param string $filePath
     */
    public function openTabularDataFileWriter($filePath);
}
