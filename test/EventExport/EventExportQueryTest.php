<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport;

class EventExportQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_not_be_empty()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Query can not be empty'
        );

        new EventExportQuery('');
    }
}
