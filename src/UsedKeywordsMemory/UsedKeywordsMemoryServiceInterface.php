<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;


interface UsedKeywordsMemoryServiceInterface
{
    public function rememberKeywordUsed($userId, $keyword);

}
