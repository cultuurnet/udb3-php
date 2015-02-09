<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileFormat;


use CultuurNet\UDB3\EventExport\FileWriter\JSONLDFileWriter;

class JSONLDFileFormat implements FileFormatInterface
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
