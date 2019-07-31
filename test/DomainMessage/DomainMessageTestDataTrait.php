<?php

namespace CultuurNet\UDB3\DomainMessage;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use PHPUnit\Framework\TestCase;
use Rhumsaa\Uuid\Uuid;

trait DomainMessageTestDataTrait
{
    /**
     * @param TestCase $testCase
     * @param string $payloadClassName
     * @return DomainMessage
     */
    private function createDomainMessage(
        TestCase $testCase,
        $payloadClassName
    ) {
        $payload = $testCase->createMock($payloadClassName);

        return new DomainMessage(
            Uuid::uuid4(),
            0,
            new Metadata(),
            $payload,
            DateTime::now()
        );
    }
}
