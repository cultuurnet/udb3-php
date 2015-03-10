<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedLabelsMemory;

use CultuurNet\UDB3\Label;

interface UsedLabelsMemoryServiceInterface
{
    /**
     * @param string $userId
     * @param Label $keyword
     * @return null
     */
    public function rememberLabelUsed($userId, Label $keyword);

    /**
     * @param string $userId
     * @return UsedLabelsMemory
     */
    public function getMemory($userId);
}
