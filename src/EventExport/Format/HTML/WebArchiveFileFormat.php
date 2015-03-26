<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

abstract class WebArchiveFileFormat
{
    /**
     * @var HTMLFileWriter
     */
    protected $htmlFileWriter;

    /**
     * @param string $brand
     * @param string $title
     * @param string|null $subtitle
     * @param string|null $footer
     * @param string|null $publisher
     */
    public function __construct(
        $brand,
        $title,
        $subtitle = null,
        $footer = null,
        $publisher = null
    ) {
        $variables = [
            'brand' => $brand,
            'title' => $title,
            'subtitle' => $subtitle,
            'footer' => $footer,
            'publisher' => $publisher,
        ];
        $this->htmlFileWriter = new HTMLFileWriter('export.html.twig', $variables);
    }

    /**
     * @return HTMLFileWriter
     */
    public function getHTMLFileWriter()
    {
        return $this->htmlFileWriter;
    }
}
