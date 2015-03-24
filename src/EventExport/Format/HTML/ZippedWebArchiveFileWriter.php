<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use Alchemy\Zippy\Zippy;

/**
 * Creates a ZIP archive of a an HTML export and all needed assets.
 *
 * Inside the zip file, all files are located in a 'html' folder.
 */
class ZippedWebArchiveFileWriter extends WebArchiveFileWriter
{
    /**
     * {@inheritdoc}
     */
    public function write($filePath, $events)
    {
        $directory = $this->createWebArchiveDirectory($events);

        $zippy = Zippy::load();
        $zippy->create(
            $filePath,
            [
                'html' => $this->expandTmpPath($directory)
            ]
        );

        $this->removeTemporaryArchiveDirectory($directory);
    }
}
