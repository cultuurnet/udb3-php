<?php

namespace CultuurNet\UDB3\Offer\Commands\Moderation;

class AbstractRejectTest extends AbstractModerationCommandTestBase
{
    /**
     * @inheritdoc
     */
    public function getModerationCommandClass()
    {
        return AbstractReject::class;
    }
}
