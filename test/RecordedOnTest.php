<?php

namespace CultuurNet\UDB3;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;

class RecordedOnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RecordedOn
     */
    private $recordedOn;

    protected function setUp()
    {
        $this->recordedOn = new RecordedOn(DateTime::fromString('2018-01-16T12:13:33Z'));
    }

    /**
     * @test
     */
    public function it_can_be_created_from_a_domain_message()
    {
        $domainMessage = new DomainMessage(
            'uuid',
            1,
            new Metadata(),
            null,
            DateTime::fromString('2018-01-16T12:13:33Z')
        );

        $this->assertEquals(
            RecordedOn::fromDomainMessage($domainMessage),
            $this->recordedOn
        );
    }

    /**
     * @test
     */
    public function it_stores_a_recorded_on_date_time()
    {
        $this->assertTrue(
            DateTime::fromString('2018-01-16T12:13:33Z')->equals(
                $this->recordedOn->getRecordedOn()
            )
        );
    }

    /**
     * @test
     */
    public function it_can_be_converted_to_a_string()
    {
        $this->assertEquals(
            '2018-01-16T12:13:33+00:00',
            $this->recordedOn->toString()
        );
    }
}
