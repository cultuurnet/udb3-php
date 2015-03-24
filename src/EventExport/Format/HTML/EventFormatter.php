<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\StringFilter\StripHtmlStringFilter;

class EventFormatter
{
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

        $formattedEvent['title'] = reset($event->name);
        $formattedEvent['description'] = $this->formatDescription(
            reset($event->description)
        );

        $formattedEvent['address'] = [
            'name' => $event->location->name,
        ];

        $formattedEvent['price'] = '119';

        /*
        'image' => 'http://media.uitdatabank.be/20140715/p18qn74oth1uvnnpidhj1i6t1f9p1.png',
        'type' => 'Cursus of workshop',
        'title' => 'De muziek van de middeleeuwen // Een middeleeuwse muziekgeschiedenis in veertig toppers',
        'description' => 'Alhoewel de middeleeuwen zo’n duizend jaar duurden, is het grootste deel van de ...',
        'dates' => 'ma 22/09/14 van 10:00 tot 12:30  ma 2...',
        'address' => array(
        'name' => 'CC De Werf',
        'street' => 'Molenstraat',
        'number' => '51',
        'postcode' => '9300',
        'municipality' => 'Aalst',
        'price' => '119,0'
        */

        return $formattedEvent;
    }

    private function formatDescription($description)
    {
        // @todo Inject the necessary filters.
        // @todo Add filter to limit amount of characters and add ...
        $filter = new StripHtmlStringFilter();

        return $filter->filter($description);
    }
}
