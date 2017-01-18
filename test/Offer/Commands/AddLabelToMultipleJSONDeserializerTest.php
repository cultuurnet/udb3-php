<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\Deserializer\DeserializerInterface;
use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

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
        $this->offerIdentifierDeserializer = $this->createMock(DeserializerInterface::class);

        $this->offerIdentifierDeserializer->expects($this->any())
            ->method('deserialize')
            ->willReturnCallback(
                function (StringLiteral $id) {
                    return new IriOfferIdentifier(
                        Url::fromNative("http://du.de/event/{$id}"),
                        $id,
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
        $json = new StringLiteral('{"label":"foo", "offers": [1, 2, 3]}');

        $expected = new AddLabelToMultiple(
            (new OfferIdentifierCollection())
                ->with(
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/1'),
                        '1',
                        OfferType::EVENT()
                    )
                )
                ->with(
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/2'),
                        '2',
                        OfferType::EVENT()
                    )
                )
                ->with(
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/3'),
                        '3',
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
        $json = new StringLiteral('{"offers":[]}');

        $this->setExpectedException(
            MissingValueException::class,
            'Missing value "label".'
        );

        $this->deserializer->deserialize($json);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_offers_are_missing()
    {
        $json = new StringLiteral('{"label":"foo"}');

        $this->setExpectedException(
            MissingValueException::class,
            'Missing value "offers".'
        );

        $this->deserializer->deserialize($json);
    }
}
