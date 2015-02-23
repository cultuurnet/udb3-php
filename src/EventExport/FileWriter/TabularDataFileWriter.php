<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;

class TabularDataFileWriter implements FileWriterInterface
{
    /**
     * @var string[]
     */
    protected $includedProperties;

    /**
     * @var TabularDataFileWriterInterface
     */
    protected $tabularDataFileWriter;

    public function __construct(
        TabularDataFileWriterInterface $tabularDataFileWriter,
        $include
    ) {
        $this->tabularDataFileWriter = $tabularDataFileWriter;
        $this->includeProperties($include);
        $this->writeHeader();
    }

    protected function writeHeader()
    {
        $columns = array();
        foreach ($this->includedProperties as $property) {
            $columns[] = $this->columns()[$property]['name'];
        }
        $this->tabularDataFileWriter->writeRow($columns);
    }

    /**
     * @param mixed $event
     */
    public function exportEvent($event)
    {
        $event = json_decode($event);
        $row = $this->emptyRow();

        foreach ($this->includedProperties as $property) {
            $column = $this->columns()[$property];
            $value = $column['include']($event);

            if ($value) {
                $row[$property] = $value;
            } else {
                $row[$property] = '';
            }
        }

        $this->tabularDataFileWriter->writeRow($row);
    }

    public function includeProperties($properties)
    {
        $this->includedProperties = $this->includedOrDefaultProperties($properties);
    }

    protected function includedOrDefaultProperties($include)
    {
        $properties = null;

        if ($include) {
            $properties = $include;
            array_unshift($properties, 'id');
        } else {
            $properties = array_keys($this->columns());
        }

        return $properties;
    }

    public function emptyRow()
    {
        $row = array();

        foreach ($this->includedProperties as $property) {
            $row[$property] = '';
        }

        return $row;
    }

    public function columns()
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
                    if ($event->bookingInfo) {
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
                    if ($event->description) {
                        return reset($event->description);
                    }
                },
                'property' => 'description'
            ],
            'organizer' => [
                'name' => 'organisatie',
                'include' => function ($event) {
                    if ($event->organizer && isset($event->organizer->name)) {
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
            'keywords' => [
                'name' => 'labels',
                'include' => function ($event) {
                    if (isset($event->keywords)) {
                        return implode(';', $event->keywords);
                    }
                },
                'property' => 'keywords'
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
                    if ($event->performer) {
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
                    if ($event->language) {
                        return implode(';', $event->language);
                    }
                },
                'property' => 'language'
            ],
            'terms.theme' => [
                'name' => 'thema',
                'include' => function ($event) {
                    if ($event->terms) {
                        foreach ($event->terms as $term) {
                            if ($term->domain && $term->label && $term->domain == 'theme') {
                                return $theme = $term->label;
                            }
                        }
                    }
                },
                'property' => 'terms.theme'
            ],
            'terms.eventtype' => [
                'name' => 'soort aanbod',
                'include' => function ($event) {
                    if ($event->terms) {
                        foreach ($event->terms as $term) {
                            if ($term->domain && $term->label && $term->domain == 'eventtype') {
                                return $eventtype = $term->label;
                            }
                        }
                    }
                },
                'property' => 'terms.eventtype'
            ],
            'created' => [
                'name' => 'datum aangemaakt',
                'include' => function ($event) {
                    return $event->created;
                },
                'property' => 'created'
            ],
            'startDate' => [
                'name' => 'startdatum',
                'include' => function ($event) {
                    return $event->startDate;
                },
                'property' => 'startDate'
            ],
            'endDate' => [
                'name' => 'einddatum',
                'include' => function ($event) {
                    return $event->endDate;
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
                    if ($event->location && isset($event->location->name)) {
                        return $event->location->name;
                    }
                },
                'property' => 'location'
            ],
            'address' => [
                'name' => 'adres',
                'include' => function ($event) {
                    if ($event->location && isset($event->location->address)) {
                        $address = [];
                        if (isset($event->location->address->streetAddress)) {
                            $address[] = $event->location->address->streetAddress;
                        }

                        $line2 = [];
                        if (isset($event->location->address->postalCode)) {
                            $line2[] = $event->location->address->postalCode;
                        }

                        if (isset($event->location->address->addressLocality)) {
                            $line2[] = $event->location->address->addressLocality;
                        }

                        if (!empty($line2)) {
                            $address[] = implode(' ', $line2);
                        }

                        if (isset($event->location->address->addressCountry)) {
                            $address[] = $event->location->address->addressCountry;
                        }

                        return implode("\r\n", $address);
                    }
                },
                'property' => 'location'
            ],
            'image' => [
                'name' => 'afbeelding',
                'include' => function ($event) {
                    return $event->image;
                },
                'property' => 'image'
            ],
            'sameAs' => [
                'name' => 'externe ids',
                'include' => function ($event) {
                    if ($event->sameAs) {
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

    /**
     * @return void
     */
    public function close()
    {
        $this->tabularDataFileWriter->close();
    }
}
