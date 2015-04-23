<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML\PDF;

use CultuurNet\UDB3\Event\ReadModel\CalendarRepositoryInterface;
use CultuurNet\UDB3\EventExport\Format\HTML\HTMLFileWriter;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo\EventInfoServiceInterface;
use CultuurNet\UDB3\EventExport\Format\HTML\WebArchive\WebArchiveFileWriter;
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
     * @param CalendarRepositoryInterface|null $calendarRepository
     */
    public function __construct(
        $princeXMLBinaryPath,
        HTMLFileWriter $htmlFileWriter,
        EventInfoServiceInterface $uitpas = null,
        CalendarRepositoryInterface $calendarRepository = null
    ) {
        parent::__construct($htmlFileWriter, $uitpas, $calendarRepository);
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
