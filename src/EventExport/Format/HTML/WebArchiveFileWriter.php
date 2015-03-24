<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\EventExport\FileWriterInterface;

abstract class WebArchiveFileWriter implements FileWriterInterface
{
    /**
     * {@inheritdoc}
     */
    public function write($filePath, $events)
    {
    }
}
