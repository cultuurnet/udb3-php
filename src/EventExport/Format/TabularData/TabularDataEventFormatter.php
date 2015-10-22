<?php

namespace CultuurNet\UDB3\EventExport\Format\TabularData;

use CultuurNet\UDB3\StringFilter\StripHtmlStringFilter;

class TabularDataEventFormatter
{
    /**
     * Class used to filter out HTML from strings.
     * @var StripHtmlStringFilter
     */
    protected $htmlFilter;

    /**
     * A list of all included properties
     * @var string[]
     */
    protected $includedProperties;

    /**
     * @param string[] $include A list of properties to include
     */
    public function __construct($include)
    {
        $this->htmlFilter = new StripHtmlStringFilter();
        $this->includedProperties = $this->includedOrDefaultProperties($include);
    }

    public function formatHeader()
    {
        $columns = array();
        foreach ($this->includedProperties as $property) {
            $columns[] = $this->columns()[$property]['name'];
        }

        return $columns;
    }

    public function formatEvent($event)
    {
        $event = json_decode($event);
        $includedProperties = $this->includedProperties;
        $row = $this->emptyRow();

        foreach ($includedProperties as $property) {
            $column = $this->columns()[$property];
            $value = $column['include']($event);

            if ($value) {
                $row[$property] = $value;
            } else {
                $row[$property] = '';
            }
        }

        return $row;
    }

    /**
     * @param string $date Date in ISO 8601 format.
     * @return string Date formatted for tabular data export.
     */
    protected function formatDate($date)
    {
        $timezone = new \DateTimeZone('Europe/Brussels');
        $datetime = \DateTime::createFromFormat(\DateTime::ISO8601, $date, $timezone);
        return $datetime->format('Y-m-d H:i');
    }

    /**
     * @param string $date Date in ISO 8601 format.
     * @return string Date formatted for tabular data export.
     */
    protected function formatDateWithoutTime($date)
    {
        $timezone = new \DateTimeZone('Europe/Brussels');
        $datetime = \DateTime::createFromFormat(\DateTime::ISO8601, $date, $timezone);
        return $datetime->format('Y-m-d');
    }

    public function emptyRow()
    {
        $row = array();

        foreach ($this->includedProperties as $property) {
            $row[$property] = '';
        }

        return $row;
    }

    protected function includedOrDefaultProperties($include)
    {
        if ($include) {
            $properties = $include;

            // include the address as separate properties
            if (($key = array_search("address", $properties)) !== false) {
                unset($properties[$key]);
                $properties[] = "address.streetAddress";
                $properties[] = "address.postalCode";
                $properties[] = "address.addressLocality";
                $properties[] = "address.addressCountry";
            }

            array_unshift($properties, 'id');
        } else {
            $properties = array_keys($this->columns());
        }

        return $properties;
    }

    protected function columns()
    {
        return [
            'id' => [
                'name' => 'id',
                'include' => function ($event) {
                    $eventUri = $event->{'@id'};
                    $uriParts = explode('/', $eventUri);
                    $eventId = array_pop($uriParts);

                    return $eventId;
                },
                'property' => 'id'
            ],
            'name' => [
                'name' => 'titel',
                'include' => function ($event) {
                    if ($event->name) {
                        return reset($event->name);
                    }
                },
                'property' => 'name'
            ],
            'creator' => [
                'name' => 'auteur',
                'include' => function ($event) {
                    return $event->creator;
                },
                'property' => 'creator'
            ],
            'bookingInfo' => [
                'name' => 'prijs',
                'include' => function ($event) {
                    if (property_exists($event, 'bookingInfo')) {
                        $firstPrice = reset($event->bookingInfo);
                        if (is_object($firstPrice) && isset($firstPrice->price)) {
                            return $firstPrice->price;
                        }
                    }
                },
                'property' => 'bookingInfo'
            ],
            'description' => [
                'name' => 'omschrijving',
                'include' => function ($event) {
                    if (property_exists($event, 'description')) {
                        $description = reset($event->description);
                        return $this->htmlFilter->filter($description);
                    }
                },
                'property' => 'description'
            ],
            'organizer' => [
                'name' => 'organisatie',
                'include' => function ($event) {
                    if (property_exists($event, 'organizer') &&
                        isset($event->organizer->name)
                    ) {
                        return $event->organizer->name;
                    }
                },
                'property' => 'organizer'
            ],
            'calendarSummary' => [
                'name' => 'tijdsinformatie',
                'include' => function ($event) {
                    return $event->calendarSummary;
                },
                'property' => 'calendarSummary'
            ],
            'labels' => [
                'name' => 'labels',
                'include' => function ($event) {
                    if (isset($event->labels)) {
                        return implode(';', $event->labels);
                    }
                },
                'property' => 'labels'
            ],
            'typicalAgeRange' => [
                'name' => 'leeftijd',
                'include' => function ($event) {
                    return $event->typicalAgeRange;
                },
                'property' => 'typicalAgeRange'
            ],
            'performer' => [
                'name' => 'uitvoerders',
                'include' => function ($event) {
                    if (property_exists($event, 'performer')) {
                        $performerNames = [];
                        foreach ($event->performer as $performer) {
                            $performerNames[] = $performer->name;
                        }

                        return implode(';', $performerNames);
                    }
                },
                'property' => 'performer'
            ],
            'language' => [
                'name' => 'taal van het aanbod',
                'include' => function ($event) {
                    if (property_exists($event, 'language')) {
                        return implode(';', $event->language);
                    }
                },
                'property' => 'language'
            ],
            'terms.theme' => [
                'name' => 'thema',
                'include' => function ($event) {
                    if (property_exists($event, 'terms')) {
                        foreach ($event->terms as $term) {
                            if ($term->domain && $term->label && $term->domain == 'theme') {
                                return $term->label;
                            }
                        }
                    }
                },
                'property' => 'terms.theme'
            ],
            'terms.eventtype' => [
                'name' => 'soort aanbod',
                'include' => function ($event) {
                    if (property_exists($event, 'terms')) {
                        foreach ($event->terms as $term) {
                            if ($term->domain && $term->label && $term->domain == 'eventtype') {
                                return $term->label;
                            }
                        }
                    }
                },
                'property' => 'terms.eventtype'
            ],
            'created' => [
                'name' => 'datum aangemaakt',
                'include' => function ($event) {
                    if (!empty($event->created)) {
                        return $this->formatDate($event->created);
                    } else {
                        return '';
                    }
                },
                'property' => 'created'
            ],
            'modified' => [
                'name' => 'datum laatste aanpassing',
                'include' => function ($event) {
                    if (!empty($event->modified)) {
                        return $this->formatDate($event->modified);
                    } else {
                        return '';
                    }
                },
                'property' => 'modified'
            ],
            'available' => [
                'name' => 'embargodatum',
                'include' => function ($event) {
                    if (!empty($event->available)) {
                        return $this->formatDateWithoutTime($event->available);
                    } else {
                        return '';
                    }
                },
                'property' => 'available'
            ],
            'startDate' => [
                'name' => 'startdatum',
                'include' => function ($event) {
                    if (!empty($event->startDate)) {
                        return $this->formatDate($event->startDate);
                    } else {
                        return '';
                    }
                },
                'property' => 'startDate'
            ],
            'endDate' => [
                'name' => 'einddatum',
                'include' => function ($event) {
                    if (!empty($event->endDate)) {
                        return $this->formatDate($event->endDate);
                    } else {
                        return '';
                    }
                },
                'property' => 'endDate'
            ],
            'calendarType' => [
                'name' => 'tijd type',
                'include' => function ($event) {
                    return $event->calendarType;
                },
                'property' => 'calendarType'
            ],
            'location' => [
                'name' => 'locatie naam',
                'include' => function ($event) {
                    if (property_exists($event, 'location') && isset($event->location->name)) {
                        return $event->location->name;
                    }
                },
                'property' => 'location'
            ],
            'address.streetAddress' => [
                'name' => 'straat',
                'include' => function ($event) {
                    if (isset($event->location->address->streetAddress)) {
                        return $event->location->address->streetAddress;
                    }
                },
                'property' => 'address.streetAddress'
            ],
            'address.postalCode' => [
                'name' => 'postcode',
                'include' => function ($event) {
                    if (isset($event->location->address->postalCode)) {
                        return $event->location->address->postalCode;
                    }
                },
                'property' => 'address.postalCode'
            ],
            'address.addressLocality' => [
                'name' => 'gemeente',
                'include' => function ($event) {
                    if (isset($event->location->address->addressLocality)) {
                        return $event->location->address->addressLocality;
                    }
                },
                'property' => 'address.addressLocality'
            ],
            'address.addressCountry' => [
                'name' => 'land',
                'include' => function ($event) {
                    if (isset($event->location->address->addressCountry)) {
                        return $event->location->address->addressCountry;
                    }
                },
                'property' => 'address.addressCountry'
            ],
            'image' => [
                'name' => 'afbeelding',
                'include' => function ($event) {
                    return !empty($event->image) ? $event->image : '';
                },
                'property' => 'image'
            ],
            'sameAs' => [
                'name' => 'externe ids',
                'include' => function ($event) {
                    if (property_exists($event, 'sameAs')) {
                        $ids = array();

                        foreach ($event->sameAs as $externalId) {
                            $ids[] = $externalId;
                        }

                        return implode("\r\n", $ids);
                    }
                },
                'property' => 'sameAs'
            ],
        ];
    }
}
