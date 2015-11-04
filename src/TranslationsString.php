<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 04/11/15
 * Time: 12:52
 */

namespace CultuurNet\UDB3;

use ValueObjects\String\String;

class TranslationsString extends String
{
    /**
     * @var array
     */
    protected $parsedData;

    /**
     * @var string
     */
    protected $lang;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $shortdescription;

    /**
     * @var string
     */
    protected $longdescription;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $value = urldecode($value);
        $value = str_replace(PHP_EOL, '', $value);
        $requiredKeys = array('lang');
        $possibleKeys = array('lang', 'title', 'shortdescription', 'longdescription');

        $data = array();
        parse_str($value, $data);
        $this->parsedData = $data;


        foreach ($requiredKeys as $key => $requiredKey) {
            if (!isset($data[$requiredKey])) {
                throw new KeyNotFoundException($requiredKey);
            }
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $possibleKeys)) {
                $this->{$key} = $value;
            }
        }

        parent::__construct($value);
    }

    /**
     * @return array
     */
    public function getParsedData()
    {
        return $this->parsedData;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getShortdescription()
    {
        return $this->shortdescription;
    }

    /**
     * @return string
     */
    public function getLongdescription()
    {
        return $this->longdescription;
    }
}
