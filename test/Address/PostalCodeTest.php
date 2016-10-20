<?php

namespace CultuurNet\UDB3\Address;

class PostalCodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_create_a_postal_code_from_a_native_string_value()
    {
        $postalCode = PostalCode::fromNative('1000');

        $expectedPostalCode = new PostalCode(1000);

        $this->assertEquals($expectedPostalCode, $postalCode);
    }

    /**
     * @test
     */
    public function it_should_create_a_postal_code_from_a_native_integer_value()
    {
        $postalCode = PostalCode::fromNative(1000);

        $expectedPostalCode = new PostalCode('1000');

        $this->assertEquals($expectedPostalCode, $postalCode);
    }
}
