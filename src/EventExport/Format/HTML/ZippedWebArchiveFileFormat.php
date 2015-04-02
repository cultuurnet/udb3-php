<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\EventExport\FileFormatInterface;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\UitpasEventInfoServiceInterface;

class ZippedWebArchiveFileFormat extends WebArchiveFileFormat implements FileFormatInterface
{
    /**
     * @var UitpasEventInfoServiceInterface|null
     */
    protected $uitpas;

    /**
     * @param string $brand
     * @param string $title
     * @param string|null $subtitle
     * @param string|null $footer
     * @param string|null $publisher
     * @param UitpasEventInfoServiceInterface|null $uitpas
     */
    public function __construct(
        $brand,
        $title,
        $subTitle = null,
        $footer = null,
        $publisher = null,
        UitpasEventInfoServiceInterface $uitpas = null
    ) {
        parent::__construct($brand, $title, $subTitle, $footer, $publisher);
        $this->uitpas = $uitpas;
    }
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
        return new ZippedWebArchiveFileWriter(
            $this->getHTMLFileWriter(),
            $this->uitpas
        );
    }
}
