<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Title;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Theme;
use ValueObjects\Geography\Country;
use ValueObjects\String\String as StringLiteral;

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
                '335be568-aaf0-4147-80b6-9267daafe23b',
                new StringLiteral('Repeteerkot'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('9630'),
                    new Locality('Zottegem'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar(
                CalendarType::PERMANENT()
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
            '335be568-aaf0-4147-80b6-9267daafe23b',
            new StringLiteral('Repeteerkot'),
            new Address(
                new Street('Kerkstraat 69'),
                new PostalCode('9630'),
                new Locality('Zottegem'),
                Country::fromNative('BE')
            )
        );
        $expectedCalendar = new Calendar(
            CalendarType::PERMANENT()
        );
        $expectedTheme = new Theme('themeid', 'theme_label');

        $this->assertEquals($expectedId, $this->updateMajorInfo->getItemId());
        $this->assertEquals($expectedTitle, $this->updateMajorInfo->getTitle());
        $this->assertEquals($expectedEventType, $this->updateMajorInfo->getEventType());
        $this->assertEquals($expectedLocation, $this->updateMajorInfo->getLocation());
        $this->assertEquals($expectedCalendar, $this->updateMajorInfo->getCalendar());
        $this->assertEquals($expectedTheme, $this->updateMajorInfo->getTheme());
    }
}
