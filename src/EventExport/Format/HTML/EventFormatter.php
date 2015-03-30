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
use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\HasUiTPASBrand;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\HasVliegBrand;
use CultuurNet\UDB3\StringFilter\CombinedStringFilter;
use CultuurNet\UDB3\StringFilter\StripHtmlStringFilter;
use CultuurNet\UDB3\StringFilter\TruncateStringFilter;

class EventFormatter
{
    /**
     * @var CombinedStringFilter
     */
    protected $filters;

    /**
     * @var EventSpecification[]
     */
    protected $iconsSpecifications;

    public function __construct()
    {
        $this->filters = new CombinedStringFilter();

        $this->filters->addFilter(new StripHtmlStringFilter());

        $truncateFilter = new TruncateStringFilter(300);
        $truncateFilter->addEllipsis();
        $truncateFilter->turnOnWordSafe(1);
        $this->filters->addFilter($truncateFilter);

        $iconSpecs = array(
            'UiTPAS'        => new HasUiTPASBrand(),
            'vlieg'         => new HasVliegBrand(),
            '1taalicoon'    => new Has1Taalicoon(),
            '2taaliconen'   => new Has2Taaliconen(),
            '3taaliconen'   => new Has3Taaliconen(),
            '4taaliconen'   => new Has4Taaliconen()
        );

        $this->iconsSpecifications = $iconSpecs;
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

        $formattedEvent['icons'] = $this->generateIcons($event);

        return $formattedEvent;
    }

    private function generateIcons($event)
    {
        return array_keys(array_filter(
            $this->iconsSpecifications,
            function ($eventSpec) use ($event) {
                /** @var EventSpecificationInterface $eventSpec */
                return $eventSpec->isSatisfiedBy($event);
            }
        ));
    }
}
