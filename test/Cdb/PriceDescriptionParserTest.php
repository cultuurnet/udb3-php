<?php

namespace CultuurNet\UDB3\Cdb;

use CommerceGuys\Intl\Currency\CurrencyRepository;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepository;

class PriceDescriptionParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PriceDescriptionParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new PriceDescriptionParser(
            new NumberFormatRepository(),
            new CurrencyRepository()
        );
    }

    /**
     * @test
     */
    public function it_parses_valid_description_into_price_key_value_pairs()
    {
        $description = 'Basistarief: 12,50 €; Met kinderen: 20,00 €; Senioren: 30,00 €';

        $expectedPrices = array(
            'Basistarief' => 12.5,
            'Met kinderen' => 20,
            'Senioren' => 30
        );

        $prices = $this->parser->parse($description);

        $this->assertEquals($expectedPrices, $prices);
    }

    /**
     * @test
     */
    public function it_ignores_invalid_descriptions()
    {
        $description = 'Met kinderen € 20, Gratis voor grootouders';

        $this->assertEmpty($this->parser->parse($description));
    }
}
