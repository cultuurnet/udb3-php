<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

class UnexpectedKeywordErrorException extends \RuntimeException
{
    public function __construct(Rsp $rsp)
    {
        parent::__construct($rsp->getMessage());
    }
}
