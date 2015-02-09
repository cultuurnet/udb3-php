<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileFormat;


use CultuurNet\UDB3\EventExport\FileWriter\JSONLDFileWriter;

class JSONLDFileFormat implements FileFormatInterface
{
    /**
     * @inheritdoc
     */
    public function getFileNameExtension()
    {
        return 'json';
    }

    /**
     * @inheritdoc
     */
    public function openWriter($filePath)
    {
        return new JSONLDFileWriter($filePath);
    }

}
