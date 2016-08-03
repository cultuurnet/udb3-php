<?php

namespace CultuurNet\UDB3\DomainMessage;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Rhumsaa\Uuid\Uuid;

trait DomainMessageTestDataTrait
{
    /**
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param string $payloadClassName
     * @return DomainMessage
     */
    private function createDomainMessage(
        \PHPUnit_Framework_TestCase $testCase,
        $payloadClassName
    ) {
        $callOriginalConstructor = false;

        $payload = $testCase->getMock($payloadClassName, [], [], '', $callOriginalConstructor);

        return new DomainMessage(
            Uuid::uuid4(),
            0,
            new Metadata(),
            $payload,
            DateTime::now()
        );
    }
}
