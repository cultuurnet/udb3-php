<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\TabularData\OOXML;

use CultuurNet\UDB3\EventExport\FileFormatInterface;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo\EventInfoServiceInterface;
use CultuurNet\UDB3\EventExport\Format\TabularData\TabularDataFileWriter;

class OOXMLFileFormat implements FileFormatInterface
{
    /**
     * @var string[]
     */
    protected $include;

    /**
     * @var EventInfoServiceInterface;
     */
    protected $uitpas;

    /**
     * @param string[]|null $include
     * @param EventInfoServiceInterface|null $uitpas
     */
    public function __construct($include = null, EventInfoServiceInterface $uitpas = null)
    {
        $this->include = $include;
        $this->uitpas = $uitpas;
    }

    /**
     * @inheritdoc
     */
    public function getFileNameExtension()
    {
        return 'xlsx';
    }

    /**
     * @inheritdoc
     */
    public function getWriter()
    {
        return new TabularDataFileWriter(
            new OOXMLFileWriterFactory(),
            $this->include,
            $this->uitpas
        );
    }
}
