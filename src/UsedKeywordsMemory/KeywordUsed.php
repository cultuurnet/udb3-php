<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;


class KeywordUsed
{

    /**
     * @var string
     */
    protected $keyword;

    /**
     * @var string
     */
    protected $userId;

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
    public function getUserId()
    {
        return $this->userId;
    }

    public function __construct($userId, $keyword)
    {
        $this->userId = $userId;
        $this->keyword = $keyword;
    }
}
