<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\EventSpecificationInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\Has1Taalicoon;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\Has2Taaliconen;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\Has3Taaliconen;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\Has4Taaliconen;
use CultuurNet\UDB3\StringFilter\CombinedStringFilter;
use CultuurNet\UDB3\StringFilter\StripHtmlStringFilter;
use CultuurNet\UDB3\StringFilter\TruncateStringFilter;
use stdClass;
use ValueObjects\String\String;

class EventFormatter
{
    /**
     * @var CombinedStringFilter
     */
    protected $filters;

    /**
     * @var EventSpecificationInterface[]
     */
    protected $taalicoonSpecs;

    /**
     * @var EventSpecificationInterface[]
     */
    protected $brandSpecs;

    /**
     * @var UitpasEventInfoServiceInterface|null
     */
    protected $uitpas;

    /**
     * @param UitpasEventInfoServiceInterface|null $uitpas
     */
    public function __construct(UitpasEventInfoServiceInterface $uitpas = null)
    {
        $this->uitpas = $uitpas;

        $this->filters = new CombinedStringFilter();

        $this->filters->addFilter(new StripHtmlStringFilter());

        $truncateFilter = new TruncateStringFilter(300);
        $truncateFilter->addEllipsis();
        $truncateFilter->turnOnWordSafe(1);
        $this->filters->addFilter($truncateFilter);

        $this->taalicoonSpecs = array(
            'EEN_TAALICOON' => new Has1Taalicoon(),
            'TWEE_TAALICONEN' => new Has2Taaliconen(),
            'DRIE_TAALICONEN' => new Has3Taaliconen(),
            'VIER_TAALICONEN' => new Has4Taaliconen()
        );

        $this->brandSpecs = array();
    }

    /**
     * @param \ValueObjects\String\String $brandName
     * @param EventSpecificationInterface $brandSpec
     */
    public function showBrand(
        String $brandName,
        EventSpecificationInterface $brandSpec
    ) {
        $brand = (string) $brandName;

        if (array_key_exists($brand, $this->brandSpecs)) {
            throw new \InvalidArgumentException('Brand name is already in use');
        }

        $this->brandSpecs[$brand] = $brandSpec;
    }

    /**
     * @param string $eventString
     *   The cultural event encoded as JSON-LD
     *
     * @return array
     *   The event as an array suitable for rendering with HTMLFileWriter
     */
    public function formatEvent($eventString)
    {
        $event = json_decode($eventString);

        $formattedEvent = [];

        if (isset($event->image)) {
            $formattedEvent['image'] = 'http:' . $event->image;
        }

        $type = EventType::fromJSONLDEvent($eventString);
        $formattedEvent['type'] = $type->getLabel();

        $formattedEvent['title'] = reset($event->name);
        $formattedEvent['description'] = $this->filters->filter(
            reset($event->description)
        );

        $formattedEvent['address'] = [
            'name' => $event->location->name,
            'street' => $event->location->address->streetAddress,
            'postcode' => $event->location->address->postalCode,
            'municipality' => $event->location->address->addressLocality,
        ];

        if (isset($event->bookingInfo)) {
            $firstPrice = reset($event->bookingInfo);
            $formattedEvent['price'] = $this->formatPrice($firstPrice->price);
        } else {
            $formattedEvent['price'] = 'Niet ingevoerd';
        }

        if (empty($formattedEvent['price'])) {
            $formattedEvent['price'] = 'Gratis';
        }

        $formattedEvent['dates'] = $event->calendarSummary;

        $this->addUitpasInfo($event, $formattedEvent);

        $this->formatTaaliconen($event, $formattedEvent);

        $formattedEvent['brands'] = $this->brand($event);

        if (isset($event->typicalAgeRange)) {
            $ageRange = $event->typicalAgeRange;
            $formattedEvent['ageFrom'] = explode('-', $ageRange)[0];
        }

        return $formattedEvent;
    }

    /**
     * @param stdClass $event
     * @param stdClass $formattedEvent
     */
    private function addUitpasInfo($event, &$formattedEvent)
    {
        if ($this->uitpas) {
            $urlParts = explode('/', $event->{'@id'});
            $eventId = end($urlParts);
            $uitpasInfo = $this->uitpas->getEventInfo($eventId);
            if ($uitpasInfo) {
                $formattedEvent['uitpas'] = [
                    'prices' => $uitpasInfo->getPrices(),
                    'advantages' => $uitpasInfo->getAdvantages()
                ];

                foreach ($formattedEvent['uitpas']['prices'] as &$price) {
                    $price['price'] = $this->formatPrice($price['price']);
                }
            }
        }
    }

    /**
     * @param mixed $price
     * @return string $price
     */
    protected function formatPrice($price)
    {
        // Limit the number of decimals to 2, and use a comma as decimal point and a dot as thousands separator.
        $price = number_format($price, 2, ',', '.');

        // Trim any insignificant zeroes after the decimal point.
        $price = trim($price, 0);

        // Trim the comma if there were only zeroes after the decimal point. Don't do this in the same trim as above, as
        // that would format 50,00 as 5.
        $price = trim($price, ',');

        return $price;
    }

    /**
     * @param $event
     * @param $formattedEvent
     */
    private function formatTaaliconen($event, &$formattedEvent)
    {
        $taalicoonCount = 0;
        $description = '';
        $i = 0;

        foreach ($this->taalicoonSpecs as $name => $spec) {
            $i++;
            /** @var EventSpecificationInterface $spec */
            if ($spec->isSatisfiedBy($event)) {
                $taalicoonCount = $i;
                $description = TaalicoonDescription::getByName($name)->getValue();
            }
        }

        if ($taalicoonCount > 0) {
            $formattedEvent['taalicoonCount'] = $taalicoonCount;
            $formattedEvent['taalicoonDescription'] = $description;
        }
    }

    private function brand($event)
    {
        return array_keys(array_filter(
            $this->brandSpecs,
            function ($brandSpec) use ($event) {
                /** @var EventSpecificationInterface $eventSpec */
                return $brandSpec->isSatisfiedBy($event);
            }
        ));
    }
}
