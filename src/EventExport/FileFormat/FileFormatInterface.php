<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileFormat;

interface FileFormatInterface
{

    /**
     * @return
     */
    public function getFileNameExtension();

    /**
     * @return \CultuurNet\UDB3\EventExport\FileWriter\FileWriterInterface
     *
     * @param string $filePath
     */
    public function openWriter($filePath);
}
