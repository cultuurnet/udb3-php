<?php

namespace CultuurNet\UDB3\EventListener;

use ValueObjects\StringLiteral\StringLiteral;

class ClassNameEventFilter implements EventFilterInterface
{
    private $classNames;

    /**
     * ClassNameCommandFilter constructor.
     * @param StringLiteral[] $classNames
     */
    public function __construct(StringLiteral ...$classNames)
    {
        $this->classNames = $classNames;
    }

    /**
     * @inheritdoc
     */
    public function matches($event)
    {
        foreach ($this->classNames as $className) {
            if (get_class($event) === $className->toNative()) {
                return true;
            }
        }

        return false;
    }
}
