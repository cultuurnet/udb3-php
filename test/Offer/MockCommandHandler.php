<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Offer\Commands\MockAddLabel;
use CultuurNet\UDB3\Offer\Commands\MockDeleteLabel;

class MockCommandHandler extends OfferCommandHandler
{
    protected function getAddLabelClassName()
    {
        return MockAddLabel::class;
    }

    protected function getDeleteLabelClassName()
    {
        return MockDeleteLabel::class;
    }
}
