<?php

namespace CultuurNet\UDB3\Label;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\DomainMessage\DomainMessageEnricherInterface;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use ValueObjects\String\String as StringLiteral;

class ImportDomainMessageEnricher implements DomainMessageEnricherInterface
{
    /**
     * @var ReadRepositoryInterface
     */
    private $readRepository;

    /**
     * @param ReadRepositoryInterface $readRepository
     */
    public function __construct(ReadRepositoryInterface $readRepository)
    {
        $this->readRepository = $readRepository;
    }

    /**
     * @param DomainMessage $domainMessage
     * @return bool
     */
    public function supports(DomainMessage $domainMessage)
    {
        return ($domainMessage->getPayload() instanceof EventImportedFromUDB2);
    }

    /**
     * @param DomainMessage $domainMessage
     * @return DomainMessage
     */
    public function enrich(DomainMessage $domainMessage)
    {
        if ($this->supports($domainMessage)) {
            $labelUuids = $this->getLabelUuids($domainMessage);

            if (count($labelUuids) > 0) {
                $extraMetadata = new Metadata(
                    [
                        'labelUuids' => $labelUuids,
                    ]
                );

                $domainMessage = $domainMessage->andMetadata($extraMetadata);
            }
        }

        return $domainMessage;
    }

    /**
     * @param DomainMessage $domainMessage
     * @return string[]
     */
    private function getLabelUuids(DomainMessage $domainMessage)
    {
        $labelUuids = [];

        /** @var EventImportedFromUDB2 $eventImportedFromUDB2 */
        $eventImportedFromUDB2 = $domainMessage->getPayload();

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $keywords = $udb2Event->getKeywords();

        foreach ($keywords as $keyword) {
            $labelDetail = $this->readRepository->getByName(
                new StringLiteral($keyword)
            );
            if ($labelDetail) {
                $labelUuids[] = $labelDetail->getName()->toNative();
            }
        }
        return $labelUuids;
    }
}
