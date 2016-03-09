<?php

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use ValueObjects\String\String;

class CreateOfferVariationJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CreateOfferVariationJSONDeserializer
     */
    private $deserializer;

    /**
     * @var IriOfferIdentifierFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $identifierFactory;

    public function setUp()
    {
        $this->identifierFactory = $this->getMock(IriOfferIdentifierFactoryInterface::class);
        $this->deserializer = new CreateOfferVariationJSONDeserializer($this->identifierFactory);
    }

    /**
     * @test
     */
    public function it_validates_the_json()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid data');

        try {
            $this->deserializer->deserialize(new String('{"owner": "foo"}'));
        } catch (ValidationException $e) {
            $this->assertEquals(
                [
                    'the property purpose is required',
                    'the property same_as is required',
                    'the property description is required',
                ],
                $e->getErrors()
            );

            throw $e;
        }
    }

    /**
     * @test
     */
    public function it_returns_a_command()
    {
        $identifier = new IriOfferIdentifier(
            '//io.uitdatabank.be/event/a0a78cd3-df53-4359-97a3-04b3680e69a4',
            'a0a78cd3-df53-4359-97a3-04b3680e69a4',
            OfferType::EVENT()
        );

        $this->identifierFactory->expects($this->once())
            ->method('fromIri')
            ->with('//io.uitdatabank.be/event/a0a78cd3-df53-4359-97a3-04b3680e69a4')
            ->willReturn($identifier);

        $command = $this->deserializer->deserialize(
            new String(
                file_get_contents(__DIR__ . '/create-event-variations.json')
            )
        );

        $expectedCommand = new CreateOfferVariation(
            $identifier,
            new OwnerId('86142ebb-1c7a-4afe-8257-6b986eb5ca21'),
            new Purpose('personal'),
            new Description('My own personal description for this event')
        );

        $this->assertEquals(
            $expectedCommand,
            $command
        );
    }
}
