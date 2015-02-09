<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;


class CSVFileWriter implements FileWriterInterface
{

    protected $f;

    protected $delimiter;

    protected $includedProperties;

    public function __construct($filePath)
    {
        $this->f = fopen($filePath, 'w');
        if (false === $this->f) {
            throw new \RuntimeException('Unable to open file for writing: ' . $filePath);
        }

        $this->delimiter = ',';

        // Overwrite default Excel delimiter.
        // UTF-16LE BOM
        fwrite($this->f, "\xFF\xFE");
        fwrite($this->f, "sep={$this->delimiter}");
        fwrite($this->f, PHP_EOL);

        $this->first = true;
    }

    public function includeProperties($properties) {
        $this->includedProperties = $this->includedOrDefaultProperties($properties);
    }

    protected function writeHeader() {
        $columns = array();
        foreach($this->includedProperties as $property) {
            $columns[] = $this->columns()[$property]['name'];
        }
        $this->writeCSV($columns);
    }

    protected function writeCSV($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = iconv('UTF-8', 'UTF-16LE//IGNORE', $value);
        }

        fputcsv($this->f, $data, $this->delimiter);
    }

    /**
     * @param mixed $event
     * @param string[] $include
     */
    public function exportEvent($event, $include)
    {
        if ($this->first) {
            $this->first = false;
            $this->includeProperties($include);
            $this->writeHeader();
        }

        $event = json_decode($event);
        $row = $this->emptyRow($include);

        foreach ($this->includedProperties as $property) {

            $column = $this->columns()[$property];

            if(isset($event->{$property}) || isset($event->{'@' . $property})) {

                $value = $column['include']($event);

                if($value) {
                    $row[$column['name']] = $value;
                } else {
                    $row[$column['name']] = '';
                }
            }
        }

        $this->writeCSV($row);
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
            $row[$this->columns()[$property]['name']] = '';
        }

        return $row;
    }

    public function columns()
    {
        return [
            'id' => [ 'name' => 'id', 'include' => function ($event) {
                $eventId = $event->{'@id'};
                var_dump('id: ' . $eventId);
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

    public function close()
    {
        if (is_resource($this->f)) {
            fclose($this->f);
        }
    }
}
