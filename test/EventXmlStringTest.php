<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 15/10/15
 * Time: 10:50
 */

namespace CultuurNet\UDB3;

class EventXmlStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventXmlString
     */
    protected $cdbxml;

    /**
     * @var string
     */
    protected $eventid;

    public function setUp()
    {
        $xml = file_get_contents(__DIR__ . '/samples/eventxmlstring_original_valid.xml');
        $this->cdbxml = new EventXmlString($xml);
        $this->eventid = "004aea08-e13d-48c9-b9eb-a18f20e6d44e";
    }

    /**
     * @test
     */
    public function it_outputs_event_xml()
    {
        $cdbxml = $this->cdbxml;
        $eventXml = $cdbxml->toEventXmlString();

        $expectedEventXml = file_get_contents(__DIR__ . '/samples/eventxmlstring_expected_event.xml');

        $this->assertXmlStringEqualsXmlString($expectedEventXml, $eventXml);
    }

    /**
     * @test
     */
    public function it_outputs_cdbxml_with_cdbid_attribute_on_event_tag()
    {
        $cdbxml = $this->cdbxml;
        $eventXmlStringWithCdbid = $cdbxml->withCdbidAttribute($this->eventid);

        $expectedEventXmlStringWithCdbid = file_get_contents(
            __DIR__ . '/samples/eventxmlstring_expected_with_cdbid.xml'
        );

        $this->assertXmlStringEqualsXmlString($expectedEventXmlStringWithCdbid, $eventXmlStringWithCdbid->toNative());
    }
}
