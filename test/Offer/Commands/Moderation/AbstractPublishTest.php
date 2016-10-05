<?php

namespace CultuurNet\UDB3\Offer\Commands\Moderation;

class AbstractPublishTest extends AbstractModerationCommandTestBase
{
    /**
     * @inheritdoc
     */
    public function getModerationCommandClass()
    {
        return AbstractPublish::class;
    }
}
