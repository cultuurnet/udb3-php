<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;


interface UsedKeywordsMemoryServiceInterface
{
    /**
     * @param string $userId
     * @param string $keyword
     * @return null
     */
    public function rememberKeywordUsed($userId, $keyword);

    /**
     * @param string $userId
     * @return UsedKeywordsMemory
     */
    public function getMemory($userId);

}
