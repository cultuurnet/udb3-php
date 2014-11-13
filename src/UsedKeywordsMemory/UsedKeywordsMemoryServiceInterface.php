<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;


use CultuurNet\UDB3\Keyword;

interface UsedKeywordsMemoryServiceInterface
{
    /**
     * @param string $userId
     * @param Keyword $keyword
     * @return null
     */
    public function rememberKeywordUsed($userId, Keyword $keyword);

    /**
     * @param string $userId
     * @return UsedKeywordsMemory
     */
    public function getMemory($userId);

}
