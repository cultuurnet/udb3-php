<?php

namespace CultuurNet\UDB3\EventExport\Command;

use CultuurNet\UDB3\EventExport\EventExportQuery;
use ValueObjects\Web\EmailAddress;

class ExportEventsAsPDF extends ExportEvents
{
    /**
     * @var string
     */
    private $brand;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $subtitle;

    /**
     * @var string
     */
    private $footer;

    /**
     * @var string
     */
    private $publisher;

    /**
     * @param EventExportQuery $query
     * @param EmailAddress|null $address
     * @param string[] $selection
     * @param string[] $include
     * @param string[] $customizations
     */
    public function __construct(
      EventExportQuery $query,
      EmailAddress $address = null,
      $selection = null,
      $include = null,
      $customizations = []
    ) {
        parent::__construct($query, $address, $selection, $include);

        $this->setCustomizations($customizations);
    }

    /**
     * @param string[] $customizations
     */
    private function setCustomizations($customizations)
    {
        $propertyNames = [
          'brand',
          'title',
          'subtitle',
          'footer',
          'publisher'
        ];
        foreach ($propertyNames as $propertyName) {
            if (isset($customizations[$propertyName])) {
                $this[$propertyName] = $customizations[$propertyName];
            } else {
                $this[$propertyName] = '';
            }
        }
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @return string
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }
}