<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\Deserializer\DeserializerInterface;
use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\String\String;

class AddLabelToMultipleJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeserializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $offerIdentifierDeserializer;

    /**
     * @var AddLabelToMultipleJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->offerIdentifierDeserializer = $this->getMock(DeserializerInterface::class);

        $this->offerIdentifierDeserializer->expects($this->any())
            ->method('deserialize')
            ->willReturnCallback(
                function (String $id) {
                    return new IriOfferIdentifier(
                        "event/{$id}",
                        OfferType::EVENT()
                    );
                }
            );

        $this->deserializer = new AddLabelToMultipleJSONDeserializer(
            $this->offerIdentifierDeserializer
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize_a_valid_add_label_to_multiple_command()
    {
        $json = new String('{"label":"foo", "offers": [1, 2, 3]}');

        $expected = new AddLabelToMultiple(
            (new OfferIdentifierCollection())
                ->with(
                    new IriOfferIdentifier(
                        'event/1',
                        OfferType::EVENT()
                    )
                )
                ->with(
                    new IriOfferIdentifier(
                        'event/2',
                        OfferType::EVENT()
                    )
                )
                ->with(
                    new IriOfferIdentifier(
                        'event/3',
                        OfferType::EVENT()
                    )
                ),
            new Label("foo")
        );

        $actual = $this->deserializer->deserialize($json);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_label_is_missing()
    {
        $json = new String('{"offers":[]}');

        $this->setExpectedException(
            MissingValueException::class,
            'Missing property "label".'
        );

        $this->deserializer->deserialize($json);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_offers_are_missing()
    {
        $json = new String('{"label":"foo"}');

        $this->setExpectedException(
            MissingValueException::class,
            'Missing property "offers".'
        );

        $this->deserializer->deserialize($json);
    }
}
