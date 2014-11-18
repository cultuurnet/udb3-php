<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;


class Rsp
{
    const LEVEL_INFO = 'INFO';

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $level;

    private function __construct($version, $level, $code, $link)
    {
        $this->code = $code;
        $this->link = $link;
        $this->version = $version;
        $this->level = $level;
    }

    static public function fromResponseBody($xml)
    {
        $simpleXml = new \SimpleXMLElement($xml);

        return new static(
            (string)$simpleXml['version'],
            (string)$simpleXml['level'],
            (string)$simpleXml->code,
            (string)$simpleXml->link
        );
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getLevel()
    {
        return $this->level;
    }
} 
