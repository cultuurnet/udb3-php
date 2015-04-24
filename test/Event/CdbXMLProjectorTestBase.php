<?php

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;

abstract class CdbXMLProjectorTestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $fileName
     * @return EventImportedFromUDB2
     */
    protected function eventImportedFromUDB2($fileName)
    {
        return $this->eventFromFile($fileName, EventImportedFromUDB2::class);
    }

    /**
     * @param $fileName
     * @return EventUpdatedFromUDB2
     */
    protected function eventUpdatedFromUDB2($fileName)
    {
        return $this->eventFromFile($fileName, EventUpdatedFromUDB2::class);
    }

    /**
     * @param string $fileName
     * @param string $eventClass
     */
    private function eventFromFile($fileName, $eventClass)
    {
        $cdbXml = file_get_contents(__DIR__ . '/' . $fileName);

        $event = new $eventClass(
            'someId',
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        return $event;
    }
}
