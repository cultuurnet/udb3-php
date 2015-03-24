<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport;

use CultuurNet\UDB3\EventExport\FileFormatInterface;
use Psr\Log\LoggerInterface;
use ValueObjects\Web\EmailAddress;

interface EventExportServiceInterface
{
    /**
     * @param FileFormatInterface $fileFormat
     * @param EventExportQuery $query
     * @param null|EmailAddress $address
     * @param null|LoggerInterface $logger
     * @param null|string[] $selection
     *
     * @return string
     *   Publicly accessible path of the file
     */
    public function exportEvents(
        FileFormatInterface $fileFormat,
        EventExportQuery $query,
        EmailAddress $address = null,
        LoggerInterface $logger = null,
        $selection = null
    );
}
