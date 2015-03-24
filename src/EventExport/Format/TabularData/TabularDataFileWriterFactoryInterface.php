<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\TabularData;

use CultuurNet\UDB3\EventExport\Format\TabularData\TabularDataFileWriterInterface;

interface TabularDataFileWriterFactoryInterface
{
    /**
     * @return TabularDataFileWriterInterface
     * @param string $filePath
     */
    public function openTabularDataFileWriter($filePath);
}
