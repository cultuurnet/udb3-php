<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Offer\Commands\AbstractSyncLabels;

class SyncLabelsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SyncLabels
     */
    private $syncLabels;

    protected function setUp()
    {
        $this->syncLabels = new SyncLabels(
            '61a9be62-abe0-11e6-80f5-76304dec7eb7',
            LabelCollection::fromStrings(
                [
                    '2dotstwice',
                    'Cultuurnet',
                ]
            )
        );
    }

    /**
     * @test
     */
    public function it_derives_from_abstract_sync_labels_command()
    {
        $this->assertInstanceOf(
            AbstractSyncLabels::class,
            $this->syncLabels
        );
    }
}
