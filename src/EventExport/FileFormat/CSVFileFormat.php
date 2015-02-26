<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileFormat;


use CultuurNet\UDB3\EventExport\FileWriter\CSVFileWriter;
use CultuurNet\UDB3\EventExport\FileWriter\TabularDataFileWriter;

class CSVFileFormat implements FileFormatInterface
{
    /**
     * @var string[]
     */
    protected $include;

    /**
     * @param string[] $include
     */
    public function __construct($include = null)
    {
        $this->include = $include;
    }

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
        return new TabularDataFileWriter(
            new CSVFileWriter($filePath),
            $this->include
        );
    }

}
