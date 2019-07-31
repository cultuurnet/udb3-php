<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations;

use PHPUnit\Framework\TestCase;

class AggregateDeletedExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function its_message_clearly_indicates_which_aggregate_was_deleted()
    {
        $exception = AggregateDeletedException::create('xyz-abc');

        $this->assertInstanceOf(AggregateDeletedException::class, $exception);
        $this->assertEquals(
            "Aggregate with id 'xyz-abc' was deleted",
            $exception->getMessage()
        );
    }
}
