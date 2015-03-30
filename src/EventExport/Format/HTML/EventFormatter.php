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
use ValueObjects\String\String;

class EventFormatter
{
    /**
     * @var CombinedStringFilter
     */
    protected $filters;

    /**
     * @var EventSpecification[]
     */
    protected $taalicoonSpecs;

    /**
     * @var EventSpecification[]
     */
    protected $brandSpecs;

    public function __construct()
    {
        $this->filters = new CombinedStringFilter();

        $this->filters->addFilter(new StripHtmlStringFilter());

        $truncateFilter = new TruncateStringFilter(300);
        $truncateFilter->addEllipsis();
        $truncateFilter->turnOnWordSafe(1);
        $this->filters->addFilter($truncateFilter);

        $this->taalicoonSpecs = array(
            new Has1Taalicoon(),
            new Has2Taaliconen(),
            new Has3Taaliconen(),
            new Has4Taaliconen()
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
            $formattedEvent['price'] = $firstPrice->price;
        } else {
            $formattedEvent['price'] = 'Niet ingevoerd';
        }

        if (empty($formattedEvent['price'])) {
            $formattedEvent['price'] = 'Gratis';
        }

        $formattedEvent['dates'] = $event->calendarSummary;

        $formattedEvent['taalicoonCount'] = $this->countTaaliconen($event);

        $formattedEvent['brands'] = $this->brand($event);

        if (isset($event->typicalAgeRange)) {
            $ageRange = $event->typicalAgeRange;
            $formattedEvent['ageFrom'] = explode('-', $ageRange)[0];
        }

        return $formattedEvent;
    }

    private function countTaaliconen($event)
    {
        $taalicoonCount = 0;

        foreach ($this->taalicoonSpecs as $index => $spec) {
            /** @var EventSpecificationInterface $spec */
            if ($spec->isSatisfiedBy($event)) {
                $taalicoonCount = $index + 1;
            }
        }

        return $taalicoonCount;
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
