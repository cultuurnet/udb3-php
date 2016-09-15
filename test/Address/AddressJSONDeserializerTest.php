<?php

namespace CultuurNet\UDB3\Address;

use CultuurNet\Deserializer\DataValidationException;
use ValueObjects\Geography\Country;
use ValueObjects\String\String as StringLiteral;

class AddressJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new AddressJSONDeserializer();
    }

    /**
     * @test
     */
    public function it_checks_all_required_fields_are_present()
    {
        $data = new StringLiteral('{}');

        $expectedException = new DataValidationException();
        $expectedException->setValidationMessages(
            [
                'streetAddress is required but could not be found.',
                'postalCode is required but could not be found.',
                'addressLocality is required but could not be found.',
                'addressCountry is required but could not be found.',
            ]
        );

        $this->deserializeAndExpectException($data, $expectedException);
    }

    /**
     * @test
     */
    public function it_checks_all_required_fields_are_not_empty()
    {
        $json = '{"streetAddress": "", "postalCode": "", "addressLocality": "", "addressCountry": ""}';
        $data = new StringLiteral($json);

        $expectedException = new DataValidationException();
        $expectedException->setValidationMessages(
            [
                'streetAddress should not be empty.',
                'postalCode should not be empty.',
                'addressLocality should not be empty.',
                'addressCountry should not be empty.',
            ]
        );

        $this->deserializeAndExpectException($data, $expectedException);
    }

    /**
     * @test
     */
    public function it_returns_an_address_object()
    {
        $data = new StringLiteral(
            json_encode(
                [
                    'streetAddress' => 'Wetstraat 1',
                    'postalCode' => '1000',
                    'addressLocality' => 'Brussel',
                    'addressCountry' => 'BE',
                ]
            )
        );

        $expectedAddress = new Address(
            Street::fromNative('Wetstraat 1'),
            PostalCode::fromNative('1000'),
            Locality::fromNative('Brussel'),
            Country::fromNative('BE')
        );

        $actualAddress = $this->deserializer->deserialize($data);

        $this->assertEquals($expectedAddress, $actualAddress);
    }

    /**
     * @param StringLiteral $data
     * @param \Exception $expectedException
     */
    private function deserializeAndExpectException(StringLiteral $data, \Exception $expectedException)
    {
        $expectedExceptionClass = get_class($expectedException);

        try {
            $this->deserializer->deserialize($data);
            $this->fail("No {$expectedExceptionClass} was thrown.");
        } catch (\Exception $e) {
            $this->assertEquals($expectedException, $e);
        }
    }
}
