<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

class CollaborationData implements SerializableInterface
{
    /**
     * @var String
     */
    protected $subBrand;

    /**
     * @var String
     */
    protected $title;

    /**
     * @var String
     */
    protected $text;

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
     */
    public function __construct(String $subBrand) {
        $this->subBrand = $subBrand;
    }

    /**
     * @return String|null
     */
    public function getSubBrand()
    {
        return $this->subBrand;
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
     * @param String $text
     * @return static
     */
    public function withText(String $text)
    {
        $c = clone $this;
        $c->text = $text;
        return $c;
    }

    /**
     * @return String|null
     */
    public function getText()
    {
        return $this->text;
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

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data)
    {
        /* @var CollaborationData $collaboration */
        $collaboration = new static(
            new String($data['subBrand'])
        );

        if (isset($data['title'])) {
            $collaboration = $collaboration
                ->withTitle(new String($data['title']));
        }

        if (isset($data['text'])) {
            $collaboration = $collaboration
                ->withText(new String($data['text']));
        }

        if (isset($data['copyright'])) {
            $collaboration = $collaboration
                ->withCopyright(new String($data['copyright']));
        }

        if (isset($data['keyword'])) {
            $collaboration = $collaboration
                ->withKeyword(new String($data['keyword']));
        }

        if (isset($data['image'])) {
            $collaboration = $collaboration
                ->withImage(new String($data['image']));
        }

        if (isset($data['article'])) {
            $collaboration = $collaboration
                ->withArticle(new String($data['article']));
        }

        if (isset($data['link'])) {
            $collaboration = $collaboration
                ->withLink(Url::fromNative($data['link']));
        }

        return $collaboration;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        $data = [
            'subBrand' => (string) $this->subBrand,
            'title' => (string) $this->title,
            'text' => (string) $this->text,
            'copyright' => (string) $this->copyright,
            'keyword' => (string) $this->keyword,
            'image' => (string) $this->image,
            'article' => (string) $this->article,
            'link' => (string) $this->link,
        ];

        return array_filter($data, 'strlen');
    }
}
