<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;


class OOXMLFileWriter implements FileWriterInterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * Next row number to write to.
     *
     * @var int
     */
    protected $i;

    /**
     * @param $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;

        $this->spreadsheet = new \PHPExcel();

        $this->spreadsheet->setActiveSheetIndex(0);

        $this->i = 1;
    }

    /**
     * @param mixed $event
     */
    public function exportEvent($event)
    {
        $this->spreadsheet->getActiveSheet()->fromArray(
            $event,
            null,
            'A' . $this->i
        );

        $this->i++;
    }

    /**
     * @return void
     */
    public function close()
    {
        $objWriter = new \PHPExcel_Writer_Excel2007($this->spreadsheet);
        $objWriter->save($this->filePath);
    }

}
