<?php

namespace CultuurNet\UDB3\Label;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\DomainMessage\DomainMessageEnricherInterface;
use CultuurNet\UDB3\Label\Events\AbstractEvent;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;

class LabelDomainMessageEnricher implements DomainMessageEnricherInterface
{
    const LABEL_NAME = 'labelName';

    /**
     * @var ReadRepositoryInterface
     */
    private $readRepository;

    /**
     * LabelDomainMessageEnricher constructor.
     * @param ReadRepositoryInterface $readRepository
     */
    public function __construct(ReadRepositoryInterface $readRepository)
    {
        $this->readRepository = $readRepository;
    }

    /**
     * @inheritdoc
     */
    public function supports(DomainMessage $domainMessage)
    {
        return ($domainMessage->getPayload() instanceof MadeVisible ||
            $domainMessage->getPayload() instanceof MadeInvisible);
    }

    /**
     * @inheritdoc
     */
    public function enrich(DomainMessage $domainMessage)
    {
        if ($this->supports($domainMessage)) {
            /** @var AbstractEvent $payload */
            $labelEvent = $domainMessage->getPayload();

            $label = $this->readRepository->getByUuid($labelEvent->getUuid());

            if ($label) {
                $extraMetadata = new Metadata(
                    [
                        'labelName' => $label->getName()->toNative(),
                    ]
                );

                $domainMessage = $domainMessage->andMetadata($extraMetadata);
            }
        }

        return $domainMessage;
    }
}
