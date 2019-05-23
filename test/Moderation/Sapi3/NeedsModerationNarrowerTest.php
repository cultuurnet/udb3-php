<?php

namespace CultuurNet\UDB3\Moderation\Sapi3;

class NeedsModerationNarrowerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itNarrowsAQueryToItemsNeedingModeration()
    {
        $narrower = new NeedsModerationNarrower();

        $narrowedQuery = $narrower->narrow('address.\*.postalCode:3000');

        $this->assertSame(
            '(address.\*.postalCode:3000) AND workflowStatus:READY_FOR_VALIDATION AND availableRange:[now TO *] AND audienceType:everyone',
            $narrowedQuery
        );
    }
}
