<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Cdb;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use CommerceGuys\Intl\Formatter\NumberFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;

/**
 * Parses a cdbxml <pricedescription> string into name value pairs.
 */
class PriceDescriptionParser
{
    /**
     * @var NumberFormatRepositoryInterface
     */
    private $numberFormatRepository;

    /**
     * @var CurrencyRepositoryInterface
     */
    private $currencyRepository;

    public function __construct(
        NumberFormatRepositoryInterface $numberFormatRepository,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $this->numberFormatRepository = $numberFormatRepository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @param string $description
     *
     * @return array
     *   An array of price name value pairs.
     */
    public function parse($description)
    {
        $prices = array();

        $possiblePriceDescriptions = preg_split('/\s*;\s*/', $description);

        $namePattern = '[\w\s]+';
        $valuePattern = '\€?\s*[\d,]+\s*\€?';

        $pricePattern =
          "/(?<name>{$namePattern}):\s*(?<value>{$valuePattern})/u";

        $numberFormat = $this->numberFormatRepository->get('nl-BE');
        $currencyFormatter = new NumberFormatter(
            $numberFormat,
            NumberFormatter::CURRENCY
        );
        $currency = $this->currencyRepository->get('EUR');

        foreach ($possiblePriceDescriptions as $possiblePriceDescription) {
            $possiblePriceDescription = trim($possiblePriceDescription);
            $matches = [];

            $priceDescriptionIsValid = preg_match(
                $pricePattern,
                $possiblePriceDescription,
                $matches
            );

            if ($priceDescriptionIsValid) {
                $priceName = trim($matches['name']);
                $priceValue = trim($matches['value']);

                $priceValue = $currencyFormatter->parseCurrency(
                    $priceValue,
                    $currency
                );

                if (false === $priceValue) {
                    continue;
                }

                if (!isset($prices[$priceName])) {
                    $prices[$priceName] = floatval($priceValue);
                }
            }
        }

        return $prices;
    }
}
