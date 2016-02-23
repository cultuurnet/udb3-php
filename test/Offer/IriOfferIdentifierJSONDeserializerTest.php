<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\Deserializer\MissingValueException;
use ValueObjects\String\String;

class IriOfferIdentifierJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IriOfferIdentifierJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new IriOfferIdentifierJSONDeserializer();
    }

    /**
     * @test
     */
    public function it_can_deserialize_a_valid_iri_offer_identifier()
    {
        $json = new String('{"@id":"event/1","@type":"Event"}');

        $expected = new IriOfferIdentifier(
            "event/1",
            OfferType::EVENT()
        );

        $actual = $this->deserializer->deserialize($json);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_id_is_missing()
    {
        $json = new String('{"@type":"Event"}');

        $this->setExpectedException(
            MissingValueException::class,
            'Missing property "@id".'
        );

        $this->deserializer->deserialize($json);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_type_is_missing()
    {
        $json = new String('{"@id":"event/1"}');

        $this->setExpectedException(
            MissingValueException::class,
            'Missing property "@type".'
        );

        $this->deserializer->deserialize($json);
    }
}
