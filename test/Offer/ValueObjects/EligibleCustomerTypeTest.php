<?php

namespace CultuurNet\UDB3\Offer\ValueObjects;

class EligibleCustomerTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_specified_options()
    {
        $options = EligibleCustomerType::getConstants();

        $this->assertEquals(
            [
                EligibleCustomerType::EVERYONE()->getName() => EligibleCustomerType::EVERYONE(),
                EligibleCustomerType::MEMBERS()->getName() => EligibleCustomerType::MEMBERS(),
                EligibleCustomerType::EDUCATION()->getName() => EligibleCustomerType::EDUCATION()
            ],
            $options
        );
    }
}
