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
     * @param EventAdvantage[] $advantages
     */
    public function __construct($prices, $advantages)
    {
        foreach ($advantages as $advantage) {
            if (!($advantage instanceof EventAdvantage)) {
                throw new \InvalidArgumentException('EventInfo advantages should be instances of EventAdvantage');
            }
        }

        $this->prices = $prices;
        $this->advantages = $advantages;
    }

    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @return EventAdvantage[]
     */
    public function getAdvantages()
    {
        return $this->advantages;
    }
}
