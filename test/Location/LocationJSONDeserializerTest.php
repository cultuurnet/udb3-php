<?php

namespace CultuurNet\UDB3\Location;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use ValueObjects\Geography\Country;
use ValueObjects\String\String as StringLiteral;

class LocationJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocationJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new LocationJSONDeserializer();
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
                'id is required but could not be found.',
                'name is required but could not be found.',
                'address is required but could not be found.',
            ]
        );

        $this->deserializeAndExpectException($data, $expectedException);
    }

    /**
     * @test
     */
    public function it_checks_all_required_fields_are_not_empty()
    {
        $json = '{"id": "", "name": "", "address": ""}';
        $data = new StringLiteral($json);

        $expectedException = new DataValidationException();
        $expectedException->setValidationMessages(
            [
                'id should not be empty.',
                'name should not be empty.',
                'address should not be empty.',
            ]
        );

        $this->deserializeAndExpectException($data, $expectedException);
    }

    /**
     * @test
     */
    public function it_returns_a_location_object()
    {
        $data = new StringLiteral(
            json_encode(
                [
                    'id' => '3941e3b6-3044-4b6c-a1af-f3a97e8af92d',
                    'name' => 'Praatcafé de Sjoemelaar',
                    'address' => [
                        'streetAddress' => 'Wetstraat 1',
                        'postalCode' => '1000',
                        'addressLocality' => 'Brussel',
                        'addressCountry' => 'BE',
                    ],
                ]
            )
        );

        $expectedLocation = new Location(
            '3941e3b6-3044-4b6c-a1af-f3a97e8af92d',
            new StringLiteral('Praatcafé de Sjoemelaar'),
            new Address(
                Street::fromNative('Wetstraat 1'),
                PostalCode::fromNative('1000'),
                Locality::fromNative('Brussel'),
                Country::fromNative('BE')
            )
        );

        $actualLocation = $this->deserializer->deserialize($data);

        $this->assertEquals($expectedLocation, $actualLocation);
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
