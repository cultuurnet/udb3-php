<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Cdb;

interface CdbXmlContainerInterface
{
    /**
     * @return string
     */
    public function getCdbXml();

    public function getCdbXmlNamespaceUri();
}
