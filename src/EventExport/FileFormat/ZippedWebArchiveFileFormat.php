<?php

namespace CultuurNet\UDB3\EventExport\FileFormat;

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
