<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


trait HasCdbXmlTrait {

    /**
     * @var string
     */
    protected $cdbXml;

    /**
     * @var string
     */
    protected $cdbXmlNamespaceUri;

    /**
     * @param string $cdbXml
     */
    private function setCdbXml($cdbXml)
    {
        $this->cdbXml = $cdbXml;
    }

    /**
     * @param string $cdbXmlNamespareUri
     */
    private function setCdbXmlNamespaceUri($cdbXmlNamespareUri)
    {
        $this->cdbXmlNamespaceUri = $cdbXmlNamespareUri;
    }

    /**
     * @return string
     */
    public function getCdbXml()
    {
        return $this->cdbXml;
    }

    /**
     * @return string
     */
    public function getCdbXmlNamespaceUri()
    {
        return $this->cdbXmlNamespaceUri;
    }
}
