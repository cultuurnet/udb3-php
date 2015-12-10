<?php

namespace CultuurNet\UDB3\CollaborationData;

use ValueObjects\String\String;
use ValueObjects\Web\Url;

class CollaborationData
{
    /**
     * @var String
     */
    protected $subbrand;

    /**
     * @var Description
     */
    protected $description;

    /**
     * @var String
     */
    protected $title;

    /**
     * @var String
     */
    protected $copyright;

    /**
     * @var
     */
    protected $url;

    /**
     * @param String $subbrand
     * @param Description $description
     */
    public function __construct(
        String $subbrand,
        Description $description
    ) {
        $this->subbrand = $subbrand;
        $this->description = $description;
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
     * @param Url $url
     * @return static
     */
    public function withUrl(Url $url)
    {
        $c = clone $this;
        $c->url = $url;
        return $c;
    }

    /**
     * @return String|null
     */
    public function getSubbrand()
    {
        return $this->subbrand;
    }

    /**
     * @return Description|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return String|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return String|null
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }
}
