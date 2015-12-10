<?php

namespace CultuurNet\UDB3\CollaborationData;

use CultuurNet\UDB3\CollaborationData\Description;

class DescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $keyword;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $image;

    /**
     * @var string
     */
    private $article;

    /**
     * @var string
     */
    private $completeDescriptionString;

    public function setUp()
    {
        $this->keyword = 'Lorem';
        $this->text = 'Lorem Ipsum Dolor Sit Amet';
        $this->image = 'lorem.png';
        $this->article = 'Lorem (2005)';

        $this->completeDescriptionString = json_encode(
            [
                'keyword' => $this->keyword,
                'text' => $this->text,
                'image' => $this->image,
                'article' => $this->article,
            ]
        );
    }

    /**
     * @test
     */
    public function it_can_extract_all_possible_json_data()
    {
        $description = new Description($this->completeDescriptionString);

        $this->assertEquals($this->keyword, $description->getKeyword());
        $this->assertEquals($this->text, $description->getText());
        $this->assertEquals($this->image, $description->getImage());
        $this->assertEquals($this->article, $description->getArticle());
    }

    /**
     * @test
     */
    public function it_defaults_missing_properties_to_an_empty_string()
    {
        $description = new Description(
            json_encode(
                ['text' => $this->text]
            )
        );

        $this->assertEquals('', $description->getKeyword());
        $this->assertEquals('', $description->getImage());
        $this->assertEquals('', $description->getArticle());
    }

    /**
     * @test
     */
    public function it_only_accepts_json_data()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        new Description('Bla bla');
    }
}
