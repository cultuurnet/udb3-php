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

    /**
     * @param string $version
     * @param string $level
     * @param string $code
     * @param string $link
     */
    private function __construct($version, $level, $code, $link)
    {
        $this->code = $code;
        $this->link = $link;
        $this->version = $version;
        $this->level = $level;
    }

    /**
     * @param string $xml
     * @return static
     */
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

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
} 
