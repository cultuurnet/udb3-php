<?php

namespace CultuurNet\UDB3\ValueObject;

class SapiVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_fixed_list_of_possible_sapi_versions(): void
    {
        $sapiVersions = SapiVersion::getConstants();

        $this->assertEquals(
            [
                SapiVersion::V2()->getName() => SapiVersion::V2,
                SapiVersion::V3()->getName() => SapiVersion::V3,
            ],
            $sapiVersions
        );
    }
}
