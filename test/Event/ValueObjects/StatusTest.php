<?php declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use CultuurNet\UDB3\Model\ValueObject\Translation\Language;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /**
     * @test
     */
    public function itCanSerialize(): void
    {
        $status = new Status(
            StatusType::unavailable(),
            [
                new StatusReason(new Language('nl'), 'Het concert van 10/11 is afgelast'),
                new StatusReason(new Language('fr'), 'Le concert de 10/11 a été annulé'),
            ]
        );

        $this->assertEquals(
            [
                'status' => 'Unavailable',
                'statusReason' => [
                    'nl' => 'Het concert van 10/11 is afgelast',
                    'fr' => 'Le concert de 10/11 a été annulé',
                ],
            ],
            $status->serialize()
        );
    }

    /**
     * @test
     */
    public function itCanDeserialize(): void
    {
        $actualStatus = Status::deserialize(
            [
                'status' => 'Unavailable',
                'statusReason' => [
                    'nl' => 'Het concert van 10/11 is afgelast',
                    'fr' => 'Le concert de 10/11 a été annulé',
                ],
            ]
        );

        $this->assertEquals(
            new Status(
                StatusType::unavailable(),
                [
                    new StatusReason(new Language('nl'), 'Het concert van 10/11 is afgelast'),
                    new StatusReason(new Language('fr'), 'Le concert de 10/11 a été annulé'),
                ]
            ),
            $actualStatus
        );
    }

    /**
     * @test
     */
    public function itCanOnlyHoldOneTranslationPerLanguage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Status(
            StatusType::unavailable(),
            [
                new StatusReason(new Language('nl'), 'Het concert van 10/11 is afgelast'),
                new StatusReason(new Language('nl'), 'Het concert van 10/11 is stiekem toch niet afgelast'),
            ]
        );
    }
}
