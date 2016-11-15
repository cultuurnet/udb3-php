<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\LabelCollection;

abstract class AbstractSyncLabels extends AbstractCommand
{
    /**
     * @var LabelCollection
     */
    protected $labels;

    /**
     * @param string $itemId
     * @param LabelCollection $labels
     */
    public function __construct($itemId, LabelCollection $labels)
    {
        parent::__construct($itemId);
        $this->labels = $labels;
    }

    /**
     * @return LabelCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }
}
