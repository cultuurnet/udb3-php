<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\StringFilter\CombinedStringFilter;
use CultuurNet\UDB3\StringFilter\StripHtmlStringFilter;
use CultuurNet\UDB3\StringFilter\TruncateStringFilter;

class EventFormatter
{
    /**
     * @var CombinedStringFilter
     */
    protected $filters;

    public function __construct()
    {
        $this->filters = new CombinedStringFilter();

        $truncateFilter = new TruncateStringFilter(300);
        $truncateFilter->addEllipsis();
        $truncateFilter->turnOnWordSafe(1);
        $this->filters->addFilter($truncateFilter);

        $this->filters->addFilter(new StripHtmlStringFilter());
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

        if ($event->image) {
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

        $formattedEvent['price'] = $event->location->bookingInfo->price;

        if (empty($formattedEvent['price'])) {
            $formattedEvent['price'] = 'Gratis';
        }

        $formattedEvent['dates'] = $event->calendarSummary;

        return $formattedEvent;
    }
}
