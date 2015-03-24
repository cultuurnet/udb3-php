<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\EventExport\FileFormatInterface;

class ZippedWebArchiveFileFormat implements FileFormatInterface
{
    /**
     * @var string
     */
    protected $brand;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $subTitle;

    /**
     * @var string
     */
    protected $footer;

    /**
     * @var string
     */
    protected $publisher;

    /**
     * @param string $brand
     * @param string $title
     * @param string|null $subTitle
     * @param string|null $footer
     * @param string|null $publisher
     */
    public function __construct(
        $brand,
        $title,
        $subTitle = null,
        $footer = null,
        $publisher = null
    ) {
        $this->brand = $brand;
        $this->title = $title;
        $this->subTitle = $subTitle;
        $this->footer = $footer;
        $this->publisher = $publisher;
    }

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
        return new ZippedWebArchiveFileWriter(
            new HTMLFileWriter(
                'export.html.twig',
                [
                    'brand' => $this->brand,
                    'title' => $this->title,
                    'subtitle' => $this->subTitle,
                    'footer' => $this->footer,
                    'publisher' => $this->publisher,
                ]
            )
        );
    }
}
