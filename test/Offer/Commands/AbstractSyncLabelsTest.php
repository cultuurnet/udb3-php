<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\LabelCollection;

class AbstractSyncLabelsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $itemId;

    /**
     * @var LabelCollection
     */
    private $labelCollection;

    /**
     * @var AbstractSyncLabels
     */
    private $abstractSyncLabels;

    protected function setUp()
    {
        $this->itemId = '61a9be62-abe0-11e6-80f5-76304dec7eb7';

        $this->labelCollection = LabelCollection::fromStrings(
            [
                '2dotstwice',
                'Cultuurnet',
            ]
        );

        $this->abstractSyncLabels = $this->getMockForAbstractClass(
            AbstractSyncLabels::class,
            [
                $this->itemId,
                $this->labelCollection,
            ]
        );
    }

    /**
     * @test
     */
    public function it_derives_from_abstract_command()
    {
        $this->assertInstanceOf(
            AbstractCommand::class,
            $this->abstractSyncLabels
        );
    }

    /**
     * @test
     */
    public function it_stores_an_item_id()
    {
        $this->assertEquals(
            $this->itemId,
            $this->abstractSyncLabels->getItemId()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_label_collection()
    {
        $this->assertEquals(
            $this->labelCollection,
            $this->abstractSyncLabels->getLabelCollection()
        );
    }
}
