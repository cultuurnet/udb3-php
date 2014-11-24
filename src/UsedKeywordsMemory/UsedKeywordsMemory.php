<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Keyword;

class UsedKeywordsMemory extends EventSourcedAggregateRoot implements \JsonSerializable
{
    protected $userId;

    /**
     * @var Keyword[]
     */
    protected $usedKeywords;

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->userId;
    }

    /**
     * @return Keyword[]
     */
    public function getKeywords()
    {
        return array_values($this->usedKeywords);
    }

    public function __construct()
    {
        $this->usedKeywords = array();
    }

    /**
     * Remember a keyword was used.
     *
     * @param Keyword $keyword
     */
    public function keywordUsed(Keyword $keyword)
    {
        $lastUsedKeyword = reset($this->usedKeywords);

        if ((string)$keyword !== (string)$lastUsedKeyword) {
            $this->apply(new KeywordUsed($this->userId, $keyword));
        }
    }

    protected function shrinkToMaximumSize()
    {
        while (count($this->usedKeywords) > 10) {
            array_pop($this->usedKeywords);
        }
    }

    /**
     * @param KeywordUsed $keywordUsed
     */
    protected function applyKeywordUsed(KeywordUsed $keywordUsed)
    {
        $key = array_search($keywordUsed->getKeyword(), $this->usedKeywords);
        if (false !== $key) {
            unset($this->usedKeywords[$key]);
        }
        array_unshift($this->usedKeywords, $keywordUsed->getKeyword());

        $this->shrinkToMaximumSize();
    }

    /**
     * @param string $userId
     * @return static
     */
    public static function create($userId)
    {
        $usedKeywordsMemory = new static();
        $usedKeywordsMemory->apply(new Created($userId));

        return $usedKeywordsMemory;
    }

    protected function applyCreated(Created $created)
    {
        $this->userId = $created->getUserId();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->usedKeywords;
    }
}
