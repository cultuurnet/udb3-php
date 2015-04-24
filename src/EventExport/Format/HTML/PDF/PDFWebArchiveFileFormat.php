<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML\PDF;

use CultuurNet\UDB3\Event\ReadModel\Calendar\CalendarRepositoryInterface;
use CultuurNet\UDB3\EventExport\FileFormatInterface;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo\EventInfoServiceInterface;
use CultuurNet\UDB3\EventExport\Format\HTML\WebArchive\WebArchiveFileFormat;

class PDFWebArchiveFileFormat extends WebArchiveFileFormat implements FileFormatInterface
{
    /**
     * @var string
     */
    protected $princeXMLBinaryPath;

    /**
     * @var EventInfoServiceInterface
     */
    protected $uitpas;

    /**
     * @var CalendarRepositoryInterface
     */
    protected $calendarRepository;

    /**
     * @param string $princeXMLBinaryPath
     * @param string $brand
     * @param string $title
     * @param string|null $subtitle
     * @param string|null $footer
     * @param string|null $publisher
     * @param EventInfoServiceInterface|null $uitpas
     * @param CalendarRepositoryInterface|null $calendarRepository
     */
    public function __construct(
        $princeXMLBinaryPath,
        $brand,
        $title,
        $subTitle = null,
        $footer = null,
        $publisher = null,
        EventInfoServiceInterface $uitpas = null,
        CalendarRepositoryInterface $calendarRepository = null
    ) {
        parent::__construct($brand, $title, $subTitle, $footer, $publisher);
        $this->princeXMLBinaryPath = $princeXMLBinaryPath;
        $this->uitpas = $uitpas;
        $this->calendarRepository = $calendarRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileNameExtension()
    {
        return 'pdf';
    }

    /**
     * {@inheritdoc}
     */
    public function getWriter()
    {
        return new PDFWebArchiveFileWriter(
            $this->princeXMLBinaryPath,
            $this->getHTMLFileWriter(),
            $this->uitpas,
            $this->calendarRepository
        );
    }
}
