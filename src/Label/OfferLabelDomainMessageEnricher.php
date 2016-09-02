<?php

namespace CultuurNet\UDB3\Label;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\DomainMessage\DomainMessageEnricherInterface;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use ValueObjects\String\String as StringLiteral;

class OfferLabelDomainMessageEnricher implements DomainMessageEnricherInterface
{
    /**
     * @var ReadRepositoryInterface
     */
    private $readRepository;

    /**
     * @param ReadRepositoryInterface $readRepository
     */
    public function __construct(
        ReadRepositoryInterface $readRepository
    ) {
        $this->readRepository = $readRepository;
    }

    /**
     * @param DomainMessage $domainMessage
     * @return bool
     */
    public function supports(DomainMessage $domainMessage)
    {
        return $domainMessage->getPayload() instanceof AbstractLabelEvent;
    }

    /**
     * @param DomainMessage $domainMessage
     * @return DomainMessage
     */
    public function enrich(DomainMessage $domainMessage)
    {
        if ($this->supports($domainMessage)) {
            /* @var AbstractLabelEvent $payload */
            $payload = $domainMessage->getPayload();

            $labelDetail = $this->readRepository->getByName(
                new StringLiteral(
                    (string) $payload->getLabel()
                )
            );

            if ($labelDetail) {
                $extraMetadata = new Metadata(
                    [
                        'labelUuid' => (string) $labelDetail->getUuid(),
                    ]
                );

                $domainMessage = $domainMessage->andMetadata($extraMetadata);
            }
        }

        return $domainMessage;
    }
}
