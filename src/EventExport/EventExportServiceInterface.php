<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport;

use Psr\Log\LoggerInterface;

interface EventExportServiceInterface
{
    /**
     * @param EventExportQuery $query
     * @param LoggerInterface $logger
     * @return string
     *   Publicly accessible path of the file
     */
    public function exportEventsAsJsonLD(
        EventExportQuery $query,
        $address = null,
        LoggerInterface $logger = null,
        $selection = null,
        $include = null
    );

    /**
     * @param EventExportQuery $query
     * @param LoggerInterface $logger
     * @return string
     *   Publicly accessible path of the file
     */
    public function exportEventsAsCSV(
        EventExportQuery $query,
        $address = null,
        LoggerInterface $logger = null,
        $selection = null,
        $include = null
    );

    /**
     * @param EventExportQuery $query
     * @param LoggerInterface $logger
     * @return string
     *   Publicly accessible path of the file
     */
    public function exportEventsAsOOXML(
        EventExportQuery $query,
        $address = null,
        LoggerInterface $logger = null,
        $selection = null,
        $include = null
    );
}
