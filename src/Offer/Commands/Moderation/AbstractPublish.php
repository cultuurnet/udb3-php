<?php

namespace CultuurNet\UDB3\Offer\Commands\Moderation;

abstract class AbstractPublish extends AbstractModerationCommand
{
    /** @var  \DateTimeInterface */
    private $embargoDate;

    /**
     * AbstractPublish constructor.
     * @param string $itemId
     * @param \DateTimeInterface|null $embargoDate
     */
    public function __construct($itemId, \DateTimeInterface $embargoDate = null)
    {
        parent::__construct($itemId);

        if (is_null($embargoDate)) {
            $embargoDate = new \DateTime();
        }
        $this->embargoDate = $embargoDate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEmbargoDate()
    {
        return $this->embargoDate;
    }
}
