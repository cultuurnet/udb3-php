<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;


class RspTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_from_a_http_xml_response()
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
<rsp version="2.0" level="INFO">
  <code>TranslationModified</code>
  <link>http://rest.uitdatabank.be/api/v2/event/ebc9eb48-da7a-4e94-8960-be2fb2a696f3</link>
</rsp>';

        $rsp = Rsp::fromResponseBody($xml);

        $this->assertEquals(
            'TranslationModified',
            $rsp->getCode()
        );

        $this->assertEquals(Rsp::LEVEL_INFO, $rsp->getLevel());
        $this->assertEquals('http://rest.uitdatabank.be/api/v2/event/ebc9eb48-da7a-4e94-8960-be2fb2a696f3', $rsp->getLink());
        $this->assertEquals('2.0', $rsp->getVersion());
    }
} 
