<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\EventExport\FileFormatInterface;

class PDFWebArchiveFileFormat extends WebArchiveFileFormat implements FileFormatInterface
{
    /**
     * @var string
     */
    protected $princeXMLBinaryPath;

    /**
     * @param string $princeXMLBinaryPath
     * {@inheritdoc}
     */
    public function __construct(
        $princeXMLBinaryPath,
        $brand,
        $title,
        $subTitle = null,
        $footer = null,
        $publisher = null
    ) {
        parent::__construct($brand, $title, $subTitle, $footer, $publisher);
        $this->princeXMLBinaryPath = $princeXMLBinaryPath;
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
            $this->getHTMLFileWriter()
        );
    }
}
