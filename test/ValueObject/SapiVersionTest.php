<?php

namespace CultuurNet\UDB3\ValueObject;

class SapiVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SapiVersion
     */
    private $sapiVersion;

    protected function setUp(): void
    {
        $this->sapiVersion = new SapiVersion(SapiVersion::V2);
    }

    /**
     * @test
     */
    public function it_can_be_converted_to_native(): void
    {
        $this->assertEquals(
            'v2',
            $this->sapiVersion->toNative()
        );
    }

    /**
     * @test
     */
    public function it_can_be_compared(): void
    {
        $sameSapiVersion = new SapiVersion(SapiVersion::V2);
        $otherSapiVersion = new SapiVersion(SapiVersion::V3);

        $this->assertTrue(
            $this->sapiVersion->equals($sameSapiVersion)
        );

        $this->assertFalse(
            $this->sapiVersion->equals($otherSapiVersion)
        );
    }

    /**
     * @test
     */
    public function it_throws_for_invalid_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sapi version "invalid" is not allowed.');

        new SapiVersion('invalid');
    }
}
