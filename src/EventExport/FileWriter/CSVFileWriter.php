<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;


class CSVFileWriter implements FileWriterInterface
{

    protected $f;

    protected $delimiter;

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

        $this->writeCSV($this->columns());
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
     */
    public function exportEvent($event)
    {
        $event = json_decode($event);

        $row = $this->emptyRow();
        $row['id'] = $event->{'@id'};
        $row['titel'] = reset($event->name);
        $row['auteur'] = $event->creator;
        if (isset($event->price)) {
            $row['prijs'] = $event->price;
        }
        $row['omschrijving'] = reset($event->description);
        if (isset($event->organizer) && isset($event->organizer->name)) {
            $row['organisatie'] = $event->organizer->name;
        }

        $row['tijdsinformatie'] = $event->calendarSummary;
        if (isset($event->keywords)) {
            if (!is_array($event->keywords)) {
                var_dump($event->{'@id'});
                var_dump($event->keywords);
            }
            $row['labels'] = implode(';', $event->keywords);
        }

        if (isset($event->typicalAgeRange)) {
            $row['leeftijd'] = $event->typicalAgeRange;
        }

        if (isset($event->performer)) {
            $performerNames = [];
            foreach ($event->performer as $performer) {
                $performerNames[] = $performer->name;
            }
            $row['uitvoerders'] = implode(';', $performerNames);
        }

        $row['taal van het aanbod'] = implode(';', $event->language);

        if (isset($event->startDate)) {
            $row['startdatum'] = $event->startDate;
        }
        if (isset($event->endDate)) {
            $row['einddatum'] = $event->endDate;
        }
        $row['tijd type'] = $event->calendarType;

        if (isset($event->location)) {
            if (isset($event->location->name)) {
                $row['locatie naam'] = $event->location->name;
            }

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

                $row['adres'] = implode("\r\n", $address);
            }
        }

        if ($event->image) {
            $row['afbeelding'] = $event->image;
        }

        $this->writeCSV($row);
    }

    public function emptyRow()
    {
        return array_fill_keys($this->columns(), '');
    }

    public function columns()
    {
        return [
            'id',
            'titel',
            'auteur',
            'prijs',
            'omschrijving',
            'organisatie',
            'tijdsinformatie',
            'labels',
            'leeftijd',
            'uitvoerders',
            'taal van het aanbod',
            'startdatum',
            'einddatum',
            'tijd type',
            'locatie naam',
            'adres',
            'afbeelding',
        ];
    }

    public function close()
    {
        if (is_resource($this->f)) {
            fclose($this->f);
        }
    }
}
