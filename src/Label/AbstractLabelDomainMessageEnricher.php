<?php

namespace CultuurNet\UDB3\Label;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\DomainMessage\DomainMessageEnricherInterface;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use ValueObjects\Identity\UUID;

abstract class AbstractLabelDomainMessageEnricher implements DomainMessageEnricherInterface
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
    public function enrich(DomainMessage $domainMessage)
    {
        if ($this->supports($domainMessage)) {
            $uuid = $this->getLabelUuid($domainMessage);
            $label = $this->readRepository->getByUuid($uuid);

            if ($label) {
                $extraMetadata = new Metadata(
                    [
                        self::LABEL_NAME => $label->getName()->toNative(),
                    ]
                );

                $domainMessage = $domainMessage->andMetadata($extraMetadata);
            }
        }

        return $domainMessage;
    }

    /**
     * @param DomainMessage $domainMessage
     * @return UUID
     */
    abstract protected function getLabelUuid(DomainMessage $domainMessage);
}
