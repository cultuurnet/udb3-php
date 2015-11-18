<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 18/11/15
 * Time: 14:18
 */

namespace test;

use CultuurNet\UDB3\LinkType;

class LinkTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_throws_an_error_for_bad_values()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Invalid link type: zottype');

        $linkType = new LinkType('zottype');
    }

    /**
     * @test
     */
    public function it_accepts_a_correct_value()
    {
        $linkType = new LinkType('roadmap');

        $this->assertEquals(
            'roadmap',
            $linkType->toNative()
        );
    }
}
