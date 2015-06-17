<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model\Properties;

use CultuurNet\UDB3\Variations\Command\ValidationException;

interface UrlValidator
{
    /**
     * @param Url $url
     * @throws ValidationException
     */
    public function validateUrl(Url $url);
}
