<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;

class OOXMLFileWriterFactory implements TabularDataFileWriterFactoryInterface
{
    /**
     * @return TabularDataFileWriterInterface
     * @param string $filePath
     */
    public function openTabularDataFileWriter($filePath)
    {
        return new OOXMLFileWriter($filePath);
    }
}
