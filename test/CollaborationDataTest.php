<?php

namespace CultuurNet\UDB3;

use ValueObjects\String\String;
use ValueObjects\Web\Url;

class CollaborationDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var String
     */
    private $subBrand;

    /**
     * @var String
     */
    private $title;

    /**
     * @var String
     */
    private $text;

    /**
     * @var String
     */
    private $copyright;

    /**
     * @var String
     */
    private $keyword;

    /**
     * @var String
     */
    private $image;

    /**
     * @var String
     */
    private $article;

    /**
     * @var Url
     */
    private $link;

    /**
     * @var CollaborationData
     */
    private $minimalCollaboration;

    /**
     * @var array
     */
    private $minimalCollaborationSerialized;

    /**
     * @var CollaborationData
     */
    private $completeCollaboration;

    /**
     * @var array
     */
    private $completeCollaborationSerialized;

    public function setUp()
    {
        $this->subBrand = new String('De Fabeltjeskrant');
        $this->title = new String('Collaborated title');
        $this->text = new String('Collaborated text');
        $this->copyright = new String('Copyright De Fabeltjeskrant');
        $this->keyword = new String('homepage');
        $this->image = new String('http://fabeltjes.krant/meneer-de-uil.png');
        $this->article = new String('10 things you did not know about Meneer De Uil');
        $this->link = Url::fromNative('http://fabeltjes.krant/10-things-you-did-not-know');

        $this->minimalCollaboration = new CollaborationData(
            $this->subBrand
        );

        $this->minimalCollaborationSerialized = [
            'subBrand' => 'De Fabeltjeskrant',
        ];

        $this->completeCollaboration = $this->minimalCollaboration
            ->withTitle($this->title)
            ->withText($this->text)
            ->withCopyright($this->copyright)
            ->withKeyword($this->keyword)
            ->withImage($this->image)
            ->withArticle($this->article)
            ->withLink($this->link);

        $this->completeCollaborationSerialized = [
            'subBrand' => 'De Fabeltjeskrant',
            'title' => 'Collaborated title',
            'text' => 'Collaborated text',
            'copyright' => 'Copyright De Fabeltjeskrant',
            'keyword' => 'homepage',
            'image' => 'http://fabeltjes.krant/meneer-de-uil.png',
            'article' => '10 things you did not know about Meneer De Uil',
            'link' => 'http://fabeltjes.krant/10-things-you-did-not-know',
        ];
    }

    /**
     * @test
     */
    public function it_returns_its_properties_or_null()
    {
        $this->assertEquals($this->subBrand, $this->minimalCollaboration->getSubBrand());
        $this->assertNull($this->minimalCollaboration->getTitle());
        $this->assertNull($this->minimalCollaboration->getText());
        $this->assertNull($this->minimalCollaboration->getCopyright());
        $this->assertNull($this->minimalCollaboration->getKeyword());
        $this->assertNull($this->minimalCollaboration->getImage());
        $this->assertNull($this->minimalCollaboration->getArticle());
        $this->assertNull($this->minimalCollaboration->getLink());

        $this->assertEquals($this->subBrand, $this->completeCollaboration->getSubBrand());
        $this->assertEquals($this->title, $this->completeCollaboration->getTitle());
        $this->assertEquals($this->text, $this->completeCollaboration->getText());
        $this->assertEquals($this->copyright, $this->completeCollaboration->getCopyright());
        $this->assertEquals($this->keyword, $this->completeCollaboration->getKeyword());
        $this->assertEquals($this->image, $this->completeCollaboration->getImage());
        $this->assertEquals($this->article, $this->completeCollaboration->getArticle());
        $this->assertEquals($this->link, $this->completeCollaboration->getLink());
    }

    /**
     * @test
     */
    public function it_can_be_serialized()
    {
        $this->assertEquals(
            $this->minimalCollaborationSerialized,
            $this->minimalCollaboration->serialize()
        );

        $this->assertEquals(
            $this->completeCollaborationSerialized,
            $this->completeCollaboration->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_be_deserialized()
    {
        $this->assertEquals(
            $this->minimalCollaboration,
            CollaborationData::deserialize(
                $this->minimalCollaborationSerialized
            )
        );

        $this->assertEquals(
            $this->completeCollaboration,
            CollaborationData::deserialize(
                $this->completeCollaborationSerialized
            )
        );
    }
}
