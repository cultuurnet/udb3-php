<?php

namespace CultuurNet\UDB3\EventListener;

interface EventFilterInterface
{
    public function matches($event);
}
