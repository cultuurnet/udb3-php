<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileFormat;

interface FileFormatInterface
{

    /**
     * Gets the file name extension applicable to the file format.
     *
     * @return string
     *   The file name extension, without the leading dot. For example: "csv".
     *
     * @link http://en.wikipedia.org/wiki/Filename_extension
     */
    public function getFileNameExtension();

    /**
     * Opens a file for exporting data to it.
     *
     * @return \CultuurNet\UDB3\EventExport\FileWriter\FileWriterInterface
     *   A file writer implementation, suitable for the file format.
     *
     * @param string $filePath
     *  The path of the file to export data to.
     */
    public function openWriter($filePath);
}
