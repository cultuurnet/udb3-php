<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\EventExport\FileFormatInterface;

class ZippedWebArchiveFileFormat extends WebArchiveFileFormat implements FileFormatInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFileNameExtension()
    {
        return 'zip';
    }

    /**
     * {@inheritdoc}
     */
    public function getWriter()
    {
        return new ZippedWebArchiveFileWriter($this->getHTMLFileWriter());
    }
}
