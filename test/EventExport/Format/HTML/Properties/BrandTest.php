<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML\Properties;

class BrandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @dataProvider brandNameDataProvider
     * @param string $brandName
     */
    public function it_can_have_a_known_brand_name($brandName)
    {
        $brand = new Brand($brandName);
        $this->assertEquals($brandName, $brand);
    }

    /**
     * @test
     */
    public function it_cannot_have_an_unknown_brand_name()
    {
        $this->setExpectedException(InvalidBrandException::class);
        new Brand('acme');
    }

    public function brandNameDataProvider()
    {
        return array(
            array('vlieg'),
            array('uit'),
            array('uitpas'),
            array('paspartoe'),
        );
    }
}
