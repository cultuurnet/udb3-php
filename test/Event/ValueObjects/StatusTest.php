<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /**
     * @test
     * @dataProvider statusProvider
     */
    public function it_can_test_for_equality(Status $otherStatus, bool $equal): void
    {
        $status = Status::cancelled();

        $this->assertEquals(
            $equal,
            $status->equals($otherStatus)
        );
    }

    public function statusProvider(): array
    {
        return [
            'equal status' => [
                Status::cancelled(),
                true,
            ],
            'different postponed status' => [
                Status::postponed(),
                false,
            ],
            'different scheduled status' => [
                Status::scheduled(),
                false,
            ],
        ];
    }

    /**
     * @test
     */
    public function ith_throws_on_unsupported_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status does not support the value "unsupported"');

        Status::fromNative('unsupported');
    }
}
