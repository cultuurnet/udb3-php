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

    public function __construct(TabularDataFileWriterInterface $tabularDataFileWriter, $include)
    {
        $this->tabularDataFileWriter = $tabularDataFileWriter;
        $this->includeProperties($include);
        $this->writeHeader();
    }

    protected function writeHeader() {
        $columns = array();
        foreach($this->includedProperties as $property) {
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

            if(isset($event->{$property}) || isset($event->{'@' . $property})) {
                $column = $this->columns()[$property];

                $value = $column['include']($event);

                if ($value) {
                    $row[$property] = $value;
                } else {
                    $row[$property] = '';
                }
            }
        }

        $this->tabularDataFileWriter->writeRow($row);
    }

    public function includeProperties($properties) {
        $this->includedProperties = $this->includedOrDefaultProperties($properties);
    }

    protected function includedOrDefaultProperties($include) {
        $properties = NULL;

        if($include) {
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

        foreach($this->includedProperties as $property) {
            $row[$property] = '';
        }

        return $row;
    }

    public function columns()
    {
        return [
            'id' => [ 'name' => 'id', 'include' => function ($event) {
                $eventUri = $event->{'@id'};
                $uriParts = explode('/',$eventUri);
                $eventId = array_pop($uriParts);
                return $eventId;
            }, 'property' => 'id' ],
            'name' => [ 'name' => 'titel', 'include' => function ($event) {
                return reset($event->name);
            }, 'property' => 'name' ],
            'creator' => [ 'name' => 'auteur', 'include' => function ($event) {
                return $event->creator;
            }, 'property' => 'creator' ],
            'bookingInfo' => [ 'name' => 'prijs', 'include' => function ($event) {
                if($event->bookingInfo && $event->bookingInfo->price) {
                    return $event->bookingInfo->price;
                }
            }, 'property' => 'bookingInfo' ],
            'description' => [ 'name' => 'omschrijving', 'include' => function ($event) {
                return reset($event->description);
            }, 'property' => 'description' ],
            'organizer' => [ 'name' => 'organisatie', 'include' => function ($event) {
                if (isset($event->organizer->name)) {
                    return $event->organizer->name;
                }
            }, 'property' => 'organizer' ],
            'calendarSummary' => [ 'name' => 'tijdsinformatie', 'include' => function ($event) {
                return $event->calendarSummary;
            }, 'property' => 'calendarSummary' ],
            'keywords' => [ 'name' => 'labels', 'include' => function ($event) {
                if (isset($event->keywords)) {
                    if (!is_array($event->keywords)) {
                        var_dump($event->{'@id'});
                        var_dump($event->keywords);
                    }
                    return implode(';', $event->keywords);
                }
            }, 'property' => 'keywords' ],
            'typicalAgeRange' => [ 'name' => 'leeftijd', 'include' => function ($event) {
                return $event->typicalAgeRange;
            }, 'property' => 'typicalAgeRange' ],
            'performer' => [ 'name' => 'uitvoerders', 'include' => function ($event) {
                $performerNames = [];
                foreach ($event->performer as $performer) {
                    $performerNames[] = $performer->name;
                }
                return implode(';', $performerNames);
            }, 'property' => 'performer' ],
            'language' => [ 'name' => 'taal van het aanbod', 'include' => function ($event) {
                return implode(';', $event->language);
            }, 'property' => 'language' ],
            'terms' => [ 'name' => 'thema', 'include' => function ($event) {
                $theme = NULL;

                if($event->terms) {
                    foreach($event->terms as $term) {
                        if($term->domain && $term->label && $term->domain == 'theme') {
                            $theme = $term->label;
                        }
                    }
                }

                return $theme;
            }, 'property' => 'terms' ],
            'created' => [ 'name' => 'datum aangemaakt', 'include' => function ($event) {
                return $event->created;
            }, 'property' => 'created' ],
            'publisher' => [ 'name' => 'auteur', 'include' => function ($event) {
                return $event->publisher;
            }, 'property' => 'publisher' ],
            'startDate' => [ 'name' => 'startdatum', 'include' => function ($event) {
                return $event->startDate;
            }, 'property' => 'startDate' ],
            'endDate' => [ 'name' => 'einddatum', 'include' => function ($event) {
                return $event->endDate;
            }, 'property' => 'endDate' ],
            'calendarType' => [ 'name' => 'tijd type', 'include' => function ($event) {
                return $event->calendarType;
            }, 'property' => 'calendarType' ],
            'location' => [ 'name' => 'locatie naam', 'include' => function ($event) {
                if (isset($event->location->name)) {
                    return $event->location->name;
                }
            }, 'property' => 'location' ],
            'address' => [ 'name' => 'adres', 'include' => function ($event) {
                if (isset($event->location->address)) {
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
            }, 'property' => 'address' ],
            'image' => [ 'name' => 'afbeelding', 'include' => function ($event) {
                return $event->image;
            }, 'property' => 'image' ],
            'sameAs' => [ 'name' => 'externe ids', 'include' => function ($event) {
                if($event->sameAs) {
                    $ids = array();

                    foreach($event->sameAs as $externalId) {
                        $ids[] = $externalId;
                    }

                    return implode("\r\n", $ids);
                }

            }, 'property' => 'sameAs' ],
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
