<?php

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\Model\Properties\DefaultUrlValidator;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\Model\Properties\UrlValidator;
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
    private $iriOfferIdentifierFactory;

    /**
     * @var DefaultUrlValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $defaultUrlValidator;

    public function setUp()
    {
        $this->deserializer = new CreateOfferVariationJSONDeserializer();
        $this->iriOfferIdentifierFactory = $this->getMock(IriOfferIdentifierFactoryInterface::class);
        $this->defaultUrlValidator = $this->getMock(UrlValidator::class);
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
        $command = $this->deserializer->deserialize(
            new String(
                file_get_contents(__DIR__ . '/create-event-variations.json')
            )
        );

        $expectedCommand = new CreateOfferVariation(
            new Url('//io.uitdatabank.be/event/a0a78cd3-df53-4359-97a3-04b3680e69a4'),
            new OwnerId('86142ebb-1c7a-4afe-8257-6b986eb5ca21'),
            new Purpose('personal'),
            new Description('My own personal description for this event')
        );

        $this->assertEquals(
            $expectedCommand,
            $command
        );
    }

    /**
     * @test
     */
    public function it_calls_the_url_validator_that_has_been_added()
    {
        $this->deserializer->addUrlValidator(
            $this->defaultUrlValidator
        );

        $this->defaultUrlValidator->expects($this->once())
            ->method('validateUrl')
            ->with(new Url('//io.uitdatabank.be/event/a0a78cd3-df53-4359-97a3-04b3680e69a4'));

        $this->deserializer->deserialize(new String(
            file_get_contents(__DIR__ . '/create-event-variations.json')
        ));
    }
}
