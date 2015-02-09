<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileFormat;


use CultuurNet\UDB3\EventExport\FileWriter\CSVFileWriter;

class CSVFileFormat implements FileFormatInterface
{
    /**
     * @inheritdoc
     */
    public function getFileNameExtension()
    {
        return 'csv';
    }

    /**
     * @inheritdoc
     */
    public function openWriter($filePath)
    {
        return new CSVFileWriter($filePath);
    }

}
