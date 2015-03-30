<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\StringFilter\CombinedStringFilter;
use CultuurNet\UDB3\StringFilter\StripHtmlStringFilter;
use CultuurNet\UDB3\StringFilter\TruncateStringFilter;
use stdClass;

class EventFormatter
{
    /**
     * @var CombinedStringFilter
     */
    protected $filters;

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

        $this->addUitpasInfo($event, $formattedEvent);

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
            }
        }
    }
}
