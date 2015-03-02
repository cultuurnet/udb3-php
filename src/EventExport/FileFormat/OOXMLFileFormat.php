<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileFormat;

use CultuurNet\UDB3\EventExport\FileWriter\OOXMLFileWriter;
use CultuurNet\UDB3\EventExport\FileWriter\TabularDataFileWriter;

class OOXMLFileFormat implements FileFormatInterface
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
        return 'xlsx';
    }

    /**
     * @inheritdoc
     */
    public function openWriter($filePath)
    {
        return new TabularDataFileWriter(
            new OOXMLFileWriter($filePath),
            $this->include
        );
    }
}
