<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;


class KeywordUsed extends Event
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

    public function __construct($userId, $keyword)
    {
        $this->userId = $userId;
        $this->keyword = $keyword;
    }

    public static function deserialize(array $data)
    {
        return new static($data['user_id'], $data['keyword']);
    }

    public function serialize()
    {
        return parent::serialize() + array(
            'keyword' => $this->keyword,
        );
    }
}
