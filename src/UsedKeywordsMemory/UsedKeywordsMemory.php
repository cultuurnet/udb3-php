<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;


use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\UsedKeywordsMemory\KeywordUsed;

class UsedKeywordsMemory extends EventSourcedAggregateRoot
{
    protected $userId;

    protected $usedKeywords;

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->userId;
    }

    public function getKeywords()
    {
        return array_values($this->usedKeywords);
    }

    public function __construct()
    {
        $this->usedKeywords = array();
    }

    public function keywordUsed($keyword)
    {
        $this->apply(new KeywordUsed($this->userId, $keyword));
    }

    protected function shrinkToMaximumSize()
    {
        while (count($this->usedKeywords) > 10) {
            array_pop($this->usedKeywords);
        }
    }

    protected function applyKeywordUsed(KeywordUsed $keywordUsed)
    {
        $key = array_search($keywordUsed->getKeyword(), $this->usedKeywords);
        if (false !== $key) {
            unset($this->usedKeywords[$key]);
        }
        array_unshift($this->usedKeywords, $keywordUsed->getKeyword());

        $this->shrinkToMaximumSize();
    }
}
