<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

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
    public function it_writes_a_file()
    {
        $events = array();

        $this->assertFileNotExists($this->filePath);

        $fileWriter = $this->createHTMLFileWriter(
            array(
                'brand' => 'uit',
                'title' => 'Lorem Ipsum.',
            )
        );
        $fileWriter->write($this->filePath, $events);

        $this->assertFileExists($this->filePath);
    }

    /**
     * @test
     * @dataProvider twigCustomTemplateProvider
     */
    public function it_can_use_a_customized_twig_environment_and_template(
        $template,
        $variables,
        $fileWithExpectedContent
    ) {
        $events = [];

        $twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem(__DIR__ . '/templates')
        );

        $fileWriter = new HTMLFileWriter(
            $template,
            $variables,
            $twig
        );

        $fileWriter->write($this->filePath, $events);

        $expected = file_get_contents($fileWithExpectedContent);
        $this->assertHTMLFileContents($expected, $this->filePath);
    }

    public function twigCustomTemplateProvider()
    {
        return [
            [
                'hello.html.twig',
                [
                    'name' => 'world'
                ],
                __DIR__ . '/results/hello-world.html',
            ],
            [
                'hello.html.twig',
                [
                    'name' => 'Belgium'
                ],
                __DIR__ . '/results/hello-belgium.html',
            ],
            [
                'goodbye.html.twig',
                [
                    'name' => 'world'
                ],
                __DIR__ . '/results/goodbye-world.html',
            ],
            [
                'goodbye.html.twig',
                [
                    'name' => 'Belgium'
                ],
                __DIR__ . '/results/goodbye-belgium.html',
            ],
        ];
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
        $fileWriter->write($this->filePath, array());

        $expected = file_get_contents(__DIR__ . '/results/export_without_events.html');
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
                    'street' => 'Molenstraat 51',
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
                    'street' => 'Hanzestedenplaats 1',
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
        $fileWriter->write($this->filePath, $events);

        $expected = file_get_contents(__DIR__ . '/results/export.html');
        $this->assertHTMLFileContents($expected, $this->filePath);
    }

    /**
     * @param array $variables
     * @return HTMLFileWriter
     */
    protected function createHTMLFileWriter($variables)
    {
        return new HTMLFileWriter(
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
