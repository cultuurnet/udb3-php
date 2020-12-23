<?php declare(strict_types=1);

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Event\ValueObjects\Status;
use CultuurNet\UDB3\Event\ValueObjects\StatusReason;
use CultuurNet\UDB3\Event\ValueObjects\StatusType;
use CultuurNet\UDB3\Language;
use PHPUnit\Framework\TestCase;

class StatusUpdatedTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_serialize(): void
    {
        $statusUpdated = new StatusUpdated(
            '542d8328-0051-4890-afbb-38b0cc8dae07',
            new Status(
                StatusType::unavailable(),
                [
                    new StatusReason(new Language('nl'), 'Nog steeds geen concerten mogelijk.'),
                    new StatusReason(new Language('en'), 'Still no concerts allowed.'),
                ]
            )
        );

        $this->assertEquals(
            [
                'id' => '542d8328-0051-4890-afbb-38b0cc8dae07',
                'status' => [
                    'type' => 'Unavailable',
                    'reason' => [
                        'nl' => 'Nog steeds geen concerten mogelijk.',
                        'en' => 'Still no concerts allowed.',
                    ],
                ],
            ],
            $statusUpdated->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize(): void
    {
        $statusUpdated = StatusUpdated::deserialize([
            'id' => '542d8328-0051-4890-afbb-38b0cc8dae07',
            'status' => [
                'type' => 'Unavailable',
                'reason' => [
                    'nl' => 'Nog steeds geen concerten mogelijk.',
                    'en' => 'Still no concerts allowed.',
                ],
            ],
        ]);

        $this->assertEquals(
            new StatusUpdated(
                '542d8328-0051-4890-afbb-38b0cc8dae07',
                new Status(
                    StatusType::unavailable(),
                    [
                        new StatusReason(new Language('nl'), 'Nog steeds geen concerten mogelijk.'),
                        new StatusReason(new Language('en'), 'Still no concerts allowed.'),
                    ]
                )
            ),
            $statusUpdated
        );
    }
}
