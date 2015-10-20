<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


class XmlStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_does_not_accept_invalid_xml()
    {
        $this->setExpectedException(XMLSyntaxException::class);

        new XmlString('this is not xml');
    }
}
