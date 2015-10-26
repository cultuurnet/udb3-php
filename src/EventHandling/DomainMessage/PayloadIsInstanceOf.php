<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventHandling\DomainMessage;

use Broadway\Domain\DomainMessage;

class PayloadIsInstanceOf implements SpecificationInterface
{
    /**
     * @var string
     */
    private $typeName;

    /**
     * @param string $typeName
     */
    public function __construct($typeName)
    {
        if (!is_string($typeName)) {
            throw new \InvalidArgumentException('Value for argument typeName should be a string');
        }
        $this->typeName = $typeName;
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy(DomainMessage $domainMessage)
    {
        $payload = $domainMessage->getPayload();

        print 'expected: ' . $this->typeName . PHP_EOL;
        print 'actual: ' . get_class($payload) . PHP_EOL;

        $satisfied = is_a($payload, $this->typeName) || is_subclass_of($payload, $this->typeName);

        print 'satisfied: ' . ($satisfied ? 'yes' : 'no') . PHP_EOL;

        return $satisfied;
    }
}
