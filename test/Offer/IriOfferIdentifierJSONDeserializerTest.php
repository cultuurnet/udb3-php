<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\Deserializer\NotWellFormedException;
use ValueObjects\String\String;

class IriOfferIdentifierJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IriOfferIdentifierJSONDeserializer
     */
    private $deserializer;

    /**
     * @var IriOfferIdentifierFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iriOfferIdentifierFactory;

    public function setUp()
    {
        $this->iriOfferIdentifierFactory = $this->getMock(IriOfferIdentifierFactoryInterface::class);
        $this->deserializer = new IriOfferIdentifierJSONDeserializer(
            $this->iriOfferIdentifierFactory
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize_a_valid_iri_offer_identifier()
    {
        $json = new String('{"@id":"event/1","@type":"Event"}');

        $expected = new IriOfferIdentifier(
            "event/1",
            "1",
            OfferType::EVENT()
        );

        $this->iriOfferIdentifierFactory->expects($this->once())
            ->method('fromIri')
            ->with('event/1')
            ->willReturn($expected);

        $actual = $this->deserializer->deserialize($json);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_the_json_is_malformed()
    {
        $json = new String('{"foo"');

        $this->setExpectedException(
            NotWellFormedException::class,
            'Invalid JSON'
        );

        $this->deserializer->deserialize($json);
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
