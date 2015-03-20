<?php

namespace CultuurNet\UDB3\Event\FileWriter;

use CultuurNet\UDB3\EventExport\FileWriter\HTMLFileWriter;

class HTMLFileWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_writes_html_to_a_file()
    {
        $filePath = $this->getFilePath();

        $fileWriter = new HTMLFileWriter($filePath, 'export.html.twig', array(
            'brand' => 'uit',
            'title' => 'Lorem Ipsum.',
        ));
        $fileWriter->close();

        $this->assertHTMLFileContents($fileWriter->getHTML(), $filePath);
    }

    /**
     * @test
     */
    public function it_inserts_variables()
    {
        $filePath = $this->getFilePath();

        $fileWriter = new HTMLFileWriter($filePath, 'export.html.twig', array(
            'brand' => 'vlieg',
            'title' => 'Lorem Ipsum.',
            'subtitle' => 'Dolor sit amet.',
            'footer' => 'Cursus mattis lorem ipsum.',
            'publisher' => 'Tellus quam porta nibh mattis.',
        ));
        $fileWriter->close();

        $expected = file_get_contents(__DIR__ . '/export_without_events.html');
        $this->assertHTMLFileContents($expected, $filePath);
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return tempnam(sys_get_temp_dir(), uniqid()) . '.html';
    }

    /**
     * @param string $html
     * @param string $filePath
     */
    protected function assertHTMLFileContents($html, $filePath)
    {
        $this->assertEquals($html, file_get_contents($filePath));
    }
}
