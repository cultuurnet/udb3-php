<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Title;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Theme;

class UpdateMajorInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateMajorInfo
     */
    protected $updateMajorInfo;

    public function setUp()
    {
        $this->updateMajorInfo = new UpdateMajorInfo(
            'id',
            new Title('title'),
            new EventType('bar_id', 'bar'),
            new Location(
                'cdbid',
                'Repeteerkot',
                'Belgium',
                'Zottegem',
                '9620',
                'De straat'
            ),
            new Calendar(
                'permanent'
            ),
            new Theme('themeid', 'theme_label')
        );
    }

    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedId = 'id';
        $expectedTitle = new Title('title');
        $expectedEventType = new EventType('bar_id', 'bar');
        $expectedLocation = new Location(
            'cdbid',
            'Repeteerkot',
            'Belgium',
            'Zottegem',
            '9620',
            'De straat'
        );
        $expectedCalendar = new Calendar(
            'permanent'
        );
        $expectedTheme = new Theme('themeid', 'theme_label');

        $this->assertEquals($expectedId, $this->updateMajorInfo->getId());
        $this->assertEquals($expectedTitle, $this->updateMajorInfo->getTitle());
        $this->assertEquals($expectedEventType, $this->updateMajorInfo->getEventType());
        $this->assertEquals($expectedLocation, $this->updateMajorInfo->getLocation());
        $this->assertEquals($expectedCalendar, $this->updateMajorInfo->getCalendar());
        $this->assertEquals($expectedTheme, $this->updateMajorInfo->getTheme());
    }
}
