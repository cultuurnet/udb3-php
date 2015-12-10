<?php

namespace CultuurNet\UDB3\CollaborationData;

use ValueObjects\String\String;

/**
 * For backwards compatibility reasons, collaboration data's description is
 * always a JSON string with several properties.
 */
class Description extends String
{
    /**
     * @var string
     */
    private $keyword = '';

    /**
     * @var string
     */
    private $text = '';

    /**
     * @var string
     */
    private $image = '';

    /**
     * @var string
     */
    private $article = '';

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $data = json_decode($value);

        if (is_null($data)) {
            throw new \InvalidArgumentException('Provided description is not valid JSON');
        }

        if (isset($data->keyword)) {
            $this->keyword = $data->keyword;
        }
        if (isset($data->text)) {
            $this->text = $data->text;
        }
        if (isset($data->image)) {
            $this->image = $data->image;
        }
        if (isset($data->article)) {
            $this->article = $data->article;
        }

        parent::__construct($value);
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getArticle()
    {
        return $this->article;
    }
}
