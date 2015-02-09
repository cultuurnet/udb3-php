<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileFormat;


use CultuurNet\UDB3\EventExport\FileWriter\OOXMLFileWriter;

class OOXMLFileFormat implements FileFormatInterface
{
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
        return new OOXMLFileWriter($filePath);
    }

}
