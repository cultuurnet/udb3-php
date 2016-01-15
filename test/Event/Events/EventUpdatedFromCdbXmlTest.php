<?php

namespace test\Event\Events;

use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\EventXmlString;
use ValueObjects\String\String;

class EventUpdatedFromCdbXmlTest extends \PHPUnit_Framework_TestCase
{
    const NS_CDBXML_3_2 = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';
    const NS_CDBXML_3_3 = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL';

    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        EventUpdatedFromCdbXml $eventUpdatedFromCdbXml
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $eventUpdatedFromCdbXml->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        EventUpdatedFromCdbXml $expectedEventUpdatedFromCdbXml
    ) {
        $this->assertEquals(
            $expectedEventUpdatedFromCdbXml,
            EventUpdatedFromCdbXml::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        $xml = file_get_contents(__DIR__ . '/../samples/event_entryapi_valid_with_keywords.xml');

        return [
            'event' => [
                [
                    'event_id' => 'test 456',
                    'cdbxml' => $xml,
                    'cdbXmlNamespaceUri' => 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL',
                ],
                new EventUpdatedFromCdbXml(
                    new String('test 456'),
                    new EventXmlString($xml),
                    new String(self::NS_CDBXML_3_3)
                ),
            ],
        ];
    }
}
