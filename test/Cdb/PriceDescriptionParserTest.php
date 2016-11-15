<?php
/**
 * @file
 */

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
    public function it_parses_valid_description_into_price_objects()
    {
        $description = 'Basistarief: 12,50 €; Met kinderen: 20,00 €; Senioren: 30,00 €';

        $basisTarief = new \CultureFeed_Cdb_Data_Price();
        $basisTarief->setTitle('Basistarief');
        $basisTarief->setValue(12.5);

        $metKinderen = new \CultureFeed_Cdb_Data_Price();
        $metKinderen->setTitle('Met kinderen');
        $metKinderen->setValue(20);

        $senioren = new \CultureFeed_Cdb_Data_Price();
        $senioren->setTitle('Senioren');
        $senioren->setValue(30);

        $expectedPrices = array(
            $basisTarief,
            $metKinderen,
            $senioren,
        );

        $prices = $this->parser->parse($description);

        $this->assertCount(3, $prices);

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
