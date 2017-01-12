<?php

namespace CultuurNet\UDB3;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class LanguageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_requires_an_iso_639_1_code()
    {
        $language = new Language('en');

        $this->assertEquals('en', $language->getCode());
    }

    /**
     * Data provider with invalid codes.
     *
     * @return array
     */
    public function invalidCodes()
    {
        return [
            ['eng'],
            ['dut'],
            [false],
            [true],
            [null],
            ['09'],
            ['whatever'],
        ];
    }

    /**
     * @test
     * @dataProvider invalidCodes
     */
    public function it_refuses_something_that_does_not_look_like_a_iso_639_1_code(
        $invalid_code
    ) {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid language code: ' . $invalid_code);

        new Language($invalid_code);
    }
}
