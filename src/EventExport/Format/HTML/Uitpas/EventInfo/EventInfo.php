<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo;

use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event\EventAdvantage;

class EventInfo
{
    /**
     * @var array
     */
    protected $prices;

    /**
     * @var EventAdvantage[]
     */
    protected $advantages;

    /**
     * @param array $prices
     * @param array $advantages
     */
    public function __construct($prices, $advantages)
    {
        $this->prices = $prices;
        $this->advantages = $advantages;
    }

    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @return array
     */
    public function getAdvantages()
    {
        return $this->advantages;
    }
}
