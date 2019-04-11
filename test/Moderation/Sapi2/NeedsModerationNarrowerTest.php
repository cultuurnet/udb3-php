<?php

namespace CultuurNet\UDB3\Moderation\Sapi2;

class NeedsModerationNarrowerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itNarrowsAQueryToItemsNeedingModeration()
    {
        $narrower = new NeedsModerationNarrower();

        $narrowedQuery = $narrower->narrow('zipcode:3000');

        $this->assertSame(
            '(zipcode:3000) AND wfstatus:"readyforvalidation" AND startdate:[NOW TO *]',
            $narrowedQuery
        );
    }
}
