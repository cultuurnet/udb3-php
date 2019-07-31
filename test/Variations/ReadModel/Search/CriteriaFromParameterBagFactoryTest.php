<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\ReadModel\Search;

use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class CriteriaFromParameterBagFactoryTest extends TestCase
{
    /**
     * @var CriteriaFromParameterBagFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new CriteriaFromParameterBagFactory();
    }

    /**
     * @test
     */
    public function it_factors_criteria()
    {
        $bag = new ParameterBag(
            ['purpose' => 'personal']
        );

        $this->assertEquals(
            (new Criteria())->withPurpose(new Purpose('personal')),
            $this->factory->createCriteriaFromParameterBag($bag)
        );

        $bag = new ParameterBag(
            ['owner' => 'BAE91055-FE91-4039-B96C-29F5661045C5']
        );

        $this->assertEquals(
            (new Criteria())->withOwnerId(new OwnerId('BAE91055-FE91-4039-B96C-29F5661045C5')),
            $this->factory->createCriteriaFromParameterBag($bag)
        );

        $bag = new ParameterBag(
            ['same_as' => '//io.uitdatabank.be/event/3A45E67A-6116-4F2F-AAD7-0B5882CF516A']
        );

        $this->assertEquals(
            (new Criteria())->withOriginUrl(
                new Url('//io.uitdatabank.be/event/3A45E67A-6116-4F2F-AAD7-0B5882CF516A')
            ),
            $this->factory->createCriteriaFromParameterBag($bag)
        );
    }
}
