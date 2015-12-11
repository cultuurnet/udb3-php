<?php

namespace CultuurNet\UDB3\CollaborationData;

use ValueObjects\String\String;
use ValueObjects\Web\Url;

class CollaborationData
{
    /**
     * @var String
     */
    protected $subBrand;

    /**
     * @var String
     */
    protected $text;

    /**
     * @var String
     */
    protected $title;

    /**
     * @var String
     */
    protected $copyright;

    /**
     * @var String
     */
    protected $keyword;

    /**
     * @var String
     */
    protected $image;

    /**
     * @var String
     */
    protected $article;

    /**
     * @var Url
     */
    protected $link;

    /**
     * @param String $subBrand
     * @param String $text
     */
    public function __construct(
        String $subBrand,
        String $text
    ) {
        $this->subBrand = $subBrand;
        $this->text = $text;
    }

    /**
     * @return String|null
     */
    public function getSubBrand()
    {
        return $this->subBrand;
    }

    /**
     * @return String|null
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param String $title
     * @return static
     */
    public function withTitle(String $title)
    {
        $c = clone $this;
        $c->title = $title;
        return $c;
    }

    /**
     * @return String|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param String $copyright
     * @return static
     */
    public function withCopyright(String $copyright)
    {
        $c = clone $this;
        $c->copyright = $copyright;
        return $c;
    }

    /**
     * @return String|null
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @param String $keyword
     * @return static
     */
    public function withKeyword(String $keyword)
    {
        $c = clone $this;
        $c->keyword = $keyword;
        return $c;
    }

    /**
     * @return String|null
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param String $article
     * @return static
     */
    public function withArticle(String $article)
    {
        $c = clone $this;
        $c->article = $article;
        return $c;
    }

    /**
     * @return String|null
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param String $image
     * @return static
     */
    public function withImage(String $image)
    {
        $c = clone $this;
        $c->image = $image;
        return $c;
    }

    /**
     * @return String
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param Url $link
     * @return static
     */
    public function withLink(Url $link)
    {
        $c = clone $this;
        $c->link = $link;
        return $c;
    }

    /**
     * @return Url
     */
    public function getLink()
    {
        return $this->link;
    }
}
