<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo\EventInfoServiceInterface;
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
     * @param HTMLFileWriter $htmlFileWriter
     * @param EventInfoServiceInterface|null $uitpas
     */
    public function __construct(
        $princeXMLBinaryPath,
        HTMLFileWriter $htmlFileWriter,
        EventInfoServiceInterface $uitpas = null
    ) {
        parent::__construct($htmlFileWriter, $uitpas);
        $this->prince = new PrinceWrapper($princeXMLBinaryPath);
    }

    /**
     * {@inheritdoc}
     */
    public function write($filePath, $events)
    {
        $originDirectory = $this->createWebArchiveDirectory($events);
        $originFile = $this->expandTmpPath($originDirectory) . '/index.html';

        $messages = array();
        $result = $this->prince->convert_file_to_file($originFile, $filePath, $messages);

        if (!$result) {
            $message = implode(PHP_EOL, $messages);
            throw new \RuntimeException($message);
        }

        $this->removeTemporaryArchiveDirectory($originDirectory);
    }
}
