<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use PrinceXMLPhp\PrinceWrapper;

/**
 * Creates a PDF file of a an HTML export and all needed assets.
 */
class PDFWebArchiveFileWriter extends WebArchiveFileWriter
{
    /**
     * @var \Prince
     */
    protected $prince;

    /**
     * @param string $princeXMLBinaryPath
     * {@inheritdoc}
     */
    public function __construct($princeXMLBinaryPath, HTMLFileWriter $htmlFileWriter)
    {
        parent::__construct($htmlFileWriter);
        $this->prince = new PrinceWrapper($princeXMLBinaryPath);
    }

    /**
     * {@inheritdoc}
     */
    public function write($filePath, $events)
    {
        $originDirectory = $this->createWebArchiveDirectory($events);
        $originFile = $this->expandTmpPath($originDirectory) . '/index.html';

        $this->prince->convert_file_to_file($originFile, $filePath);

        $this->removeTemporaryArchiveDirectory($originDirectory);
    }
}
