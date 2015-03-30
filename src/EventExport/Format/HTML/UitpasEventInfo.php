<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

class UitpasEventInfo
{
    /**
     * @var array
     */
    protected $prices;

    /**
     * @var array
     */
    protected $advantages;

    /**
     * @param array $prices
     * @param array $advantages
     */
    public function __construct($prices, $advantages)
    {
        $this->prices = $prices;
    }

    public function getPrices()
    {
        return $this->prices;
    }

    public function getAdvantages()
    {
        return $this->advantages;
    }
}
