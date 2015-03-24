<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\TabularData\CSV;

use CultuurNet\UDB3\EventExport\Format\TabularData\TabularDataFileWriterFactoryInterface;
use CultuurNet\UDB3\EventExport\Format\TabularData\TabularDataFileWriterInterface;
use CultuurNet\UDB3\EventExport\Format\TabularData\CSV\CSVFileWriter;

class CSVFileWriterFactory implements TabularDataFileWriterFactoryInterface
{
    /**
     * @return \CultuurNet\UDB3\EventExport\Format\TabularData\TabularDataFileWriterInterface
     * @param string $filePath
     */
    public function openTabularDataFileWriter($filePath)
    {
        return new CSVFileWriter($filePath);
    }
}
