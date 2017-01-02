<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Offer\Commands\AbstractCommand;

class UpdateAudienceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AudienceType
     */
    private $audienceType;

    /**
     * @var UpdateAudience
     */
    private $updateAudience;

    protected function setUp()
    {
        $this->audienceType = AudienceType::EDUCATION();

        $this->updateAudience = new UpdateAudience(
            '6eaaa9b6-d0d2-11e6-bf26-cec0c932ce01',
            $this->audienceType
        );
    }

    /**
     * @test
     */
    public function it_derives_from_abstract_command()
    {
        $this->assertInstanceOf(
            AbstractCommand::class,
            $this->updateAudience
        );
    }

    /**
     * @test
     */
    public function it_stores_an_audience_type()
    {
        $this->assertEquals(
            $this->audienceType,
            $this->updateAudience->getAudienceType()
        );
    }
}
