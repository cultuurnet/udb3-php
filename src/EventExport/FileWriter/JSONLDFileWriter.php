<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\FileWriter;

class JSONLDFileWriter implements FileWriterInterface
{
    protected $f;

    /**
     * @var string[]
     */
    protected $includedProperties;

    /**
     * @var string[]
     */
    protected $includedTerms;

    public function __construct($filePath, $include = null)
    {
        $this->f = fopen($filePath, 'w');
        if (false === $this->f) {
            throw new \RuntimeException(
                'Unable to open file for writing: ' . $filePath
            );
        }
        fwrite($this->f, '[');

        $this->first = true;

        if ($include) {
            $include[] = '@id';
            // The address property is nested inside location.
            // The whole location property gets included instead of pulling it
            // out and placing it directly on the object.
            if (in_array('address', $include) &&
                !in_array('location', $include)
            ) {
                array_push($include, 'location');
            }

            $terms = $this->filterTermsFromProperties($include);
            if (count($terms) > 0) {
                $this->includedTerms = $terms;
                $include[] = 'terms';
            }

            $this->includedProperties = $include;
        }

    }

    private function filterTermsFromProperties($properties)
    {
        $termPrefix = 'terms.';

        $prefixedTerms = array_filter(
            $properties,
            function($property) use ($termPrefix) {
                return strpos($property, $termPrefix) === 0;
            }
        );
        $terms = array_map(function($term) use ($termPrefix) {
            return str_replace($termPrefix, "", $term);
        }, $prefixedTerms);

        return $terms;
    }

    /**
     * @param mixed $event
     */
    public function exportEvent($event)
    {
        if ($this->first) {
            $this->first = false;
        } else {
            fwrite($this->f, ',');
        }
        
        $includedProperties = $this->includedProperties;
        $includedTerms = $this->includedTerms;

        if ($includedProperties) {
            $eventObject = json_decode($event);

            // filter out terms
            if (property_exists($eventObject, 'terms') && $includedTerms) {
                $filteredTerms = array_filter(
                    $eventObject->terms,
                    function ($term) use ($includedTerms) {
                        return in_array($term->domain, $includedTerms);
                    }
                );

                $eventObject->terms = array_values($filteredTerms);
            }
            
            // filter out base propoerties
            foreach ($eventObject as $propertyName => $value) {
                if (!in_array($propertyName, $includedProperties)) {
                    unset($eventObject->{$propertyName});
                }
            }

            

            $event = json_encode($eventObject);
        }

        fwrite($this->f, $event);
    }

    public function close()
    {
        if (is_resource($this->f)) {
            fwrite($this->f, ']');

            fclose($this->f);
        }
    }
}
