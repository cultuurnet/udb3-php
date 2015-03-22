<?php

namespace CultuurNet\UDB3\Event\FileWriter;

use CultuurNet\UDB3\EventExport\FileWriter\HTMLFileWriter;

class HTMLFileWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $filePath;

    public function setUp()
    {
        parent::setUp();
        $this->filePath = $this->getFilePath();
    }

    /**
     * @test
     */
    public function it_writes_html_to_a_file()
    {
        $fileWriter = $this->createHTMLFileWriter(
            array(
                'brand' => 'uit',
                'title' => 'Lorem Ipsum.',
            )
        );
        $fileWriter->close();

        $this->assertHTMLFileContents($fileWriter->getHTML(), $this->filePath);
    }

    /**
     * @test
     */
    public function it_inserts_variables()
    {
        $fileWriter = $this->createHTMLFileWriter(
            array(
                'brand' => 'vlieg',
                'title' => 'Lorem Ipsum.',
                'subtitle' => 'Dolor sit amet.',
                'footer' => 'Cursus mattis lorem ipsum.',
                'publisher' => 'Tellus quam porta nibh mattis.',
            )
        );
        $fileWriter->close();

        $expected = file_get_contents(__DIR__ . '/export_without_events.html');
        $this->assertHTMLFileContents($expected, $this->filePath);
    }

    /**
     * @test
     */
    public function it_inserts_events()
    {
        $events = array(
            array(
                'image' => 'http://media.uitdatabank.be/20140715/p18qn74oth1uvnnpidhj1i6t1f9p1.png',
                'type' => 'Cursus of workshop',
                'title' => 'De muziek van de middeleeuwen // Een middeleeuwse muziekgeschiedenis in veertig toppers',
                'description' => 'Alhoewel de middeleeuwen zo’n duizend jaar duurden, is het grootste deel van de ...',
                'dates' => 'ma 22/09/14 van 10:00 tot 12:30  ma 2...',
                'address' => array(
                    'name' => 'CC De Werf',
                    'street' => 'Molenstraat',
                    'number' => '51',
                    'postcode' => '9300',
                    'municipality' => 'Aalst',
                ),
                'price' => '119,0',
            ),
            array(
                'image' => 'http://media.uitdatabank.be/20130805/8d455579-2207-4643-bdaf-a514da64697b.JPG',
                'type' => 'Spel of quiz',
                'title' => 'Speurtocht Kapitein Massimiliaan en de vliegende Hollander',
                'description' => 'Een familiespel voor jong en oud! Worden jullie de nieuwe matrozen van de ...',
                'dates' => 'elke zo, di, woe, do, vrij, za van 10...',
                'address' => array(
                    'name' => 'Museum aan de Stroom (MAS)',
                    'street' => 'Hanzestedenplaats',
                    'number' => '1',
                    'postcode' => '2000',
                    'municipality' => 'Antwerpen',
                ),
                'price' => 'Gratis',
            ),
        );

        $fileWriter = $this->createHTMLFileWriter(
            array(
                'brand' => 'uit',
                'title' => 'Lorem Ipsum.',
            )
        );
        $fileWriter->exportEvents($events);
        $fileWriter->close();

        $expected = file_get_contents(__DIR__ . '/export.html');
        $this->assertHTMLFileContents($expected, $this->filePath);
    }

    /**
     * @param array $variables
     * @return HTMLFileWriter
     */
    protected function createHTMLFileWriter($variables)
    {
        return new HTMLFileWriter(
            $this->filePath,
            'export.html.twig',
            $variables
        );
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

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        if ($this->filePath && file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
}
