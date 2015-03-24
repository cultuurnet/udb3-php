<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\EventExport\FileFormatInterface;

class ZippedWebArchiveFileFormat implements FileFormatInterface
{
    /**
     * @inheritdoc
     */
    public function getFileNameExtension()
    {
        return 'zip';
    }

    /**
     * @inheritdoc
     */
    public function getWriter()
    {

    }
}
