<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;


class PrivateKeywordException extends \RuntimeException
{
    public function __construct(Rsp $rsp)
    {
        parent::__construct($rsp->getMessage());
    }
}
