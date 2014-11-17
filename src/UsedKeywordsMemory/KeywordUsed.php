<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;


use CultuurNet\UDB3\Keyword;

class KeywordUsed extends Event
{

    /**
     * @var Keyword
     */
    protected $keyword;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @return Keyword
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param string $userId
     * @param Keyword $keyword
     */
    public function __construct($userId, Keyword $keyword)
    {
        $this->userId = $userId;
        $this->keyword = $keyword;
    }

    public static function deserialize(array $data)
    {
        $keyword = new Keyword($data['keyword']);
        // compatibility layer end
        return new static($data['user_id'], $keyword);
    }

    public function serialize()
    {
        return parent::serialize() + array(
            'keyword' => (string)$this->keyword,
        );
    }
}
