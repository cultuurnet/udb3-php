<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\LabelCollection;

abstract class AbstractSyncLabels extends AbstractCommand
{
    /**
     * @var LabelCollection
     */
    protected $labelCollection;

    /**
     * @param string $itemId
     * @param LabelCollection $labelCollection
     */
    public function __construct($itemId, LabelCollection $labelCollection)
    {
        parent::__construct($itemId);
        $this->labelCollection = $labelCollection;
    }

    /**
     * @return LabelCollection
     */
    public function getLabelCollection()
    {
        return $this->labelCollection;
    }
}
