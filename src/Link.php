<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 19/11/15
 * Time: 21:36
 */

namespace Cultuurnet\UDB3;

use ValueObjects\String\String;

class Link
{
    /**
     * @var String|String
     */
    protected $link;

    /**
     * @var LinkType
     */
    protected $linkType;

    /**
     * @var String|String
     */
    protected $title;

    /**
     * @var String|String
     */
    protected $copyright;

    /**
     * @var String|String
     */
    protected $subbrand;

    /**
     * @var String|String
     */
    protected $description;

    public function __construct(
        String $link,
        LinkType $linkType,
        String $title = null,
        String $copyright = null,
        String $subbrand = null,
        String $description = null
    ) {
        $this->link = $link;
        $this->linkType = $linkType;
        $this->title = $title;
        $this->copyright = $copyright;
        $this->subbrand = $subbrand;
        $this->description = $description;
    }

    /**
     * @return String
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return LinkType
     */
    public function getLinkType()
    {
        return $this->linkType;
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
     * @return String|null
     */
    public function getSubbrand()
    {
        return $this->subbrand;
    }

    /**
     * @return String|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
