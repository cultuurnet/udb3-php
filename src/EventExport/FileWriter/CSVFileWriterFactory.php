<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;

class CSVFileWriterFactory implements TabularDataFileWriterFactoryInterface
{
    /**
     * @return TabularDataFileWriterInterface
     * @param string $filePath
     */
    public function openTabularDataFileWriter($filePath)
    {
        return new CSVFileWriter($filePath);
    }
}
