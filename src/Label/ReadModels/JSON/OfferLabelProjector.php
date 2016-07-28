<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\LabelDomainMessageEnricher;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\ReadRepositoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\Identity\UUID;

class OfferLabelProjector implements EventListenerInterface, LoggerAwareInterface
{
    use DelegateEventHandlingToSpecificMethodTrait {
        DelegateEventHandlingToSpecificMethodTrait::handle as handleSpecific;
    }

    use LoggerAwareTrait;

    /**
     * @var ReadRepositoryInterface
     */
    private $relationRepository;

    /**
     * @var DocumentRepositoryInterface
     */
    private $offerRepository;

    /**
     * OfferLabelProjector constructor.
     * @param DocumentRepositoryInterface $offerRepository
     * @param ReadRepositoryInterface $relationRepository
     */
    public function __construct(
        DocumentRepositoryInterface $offerRepository,
        ReadRepositoryInterface $relationRepository
    ) {
        $this->offerRepository = $offerRepository;
        $this->relationRepository = $relationRepository;
        $this->logger = new NullLogger();
    }

    /**
     * @param DomainMessage $domainMessage
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();

        if ($event instanceof MadeVisible) {
            $this->applyMadeVisible($domainMessage->getPayload(), $domainMessage->getMetadata());
        } else if ($event instanceof MadeInvisible) {
            $this->applyMadeInvisible($domainMessage->getPayload(), $domainMessage->getMetadata());
        } else {
            $this->handleSpecific($domainMessage);
        }
    }

    /**
     * @param MadeVisible $madeVisible
     * @param Metadata $metaData
     */
    public function applyMadeVisible(MadeVisible $madeVisible, Metadata $metaData)
    {
        $this->updateLabels($madeVisible->getUuid(), $metaData, true);
    }

    /**
     * @param MadeInvisible $madeInvisible
     * @param Metadata $metaData
     */
    public function applyMadeInvisible(MadeInvisible $madeInvisible, Metadata $metaData)
    {
        $this->updateLabels($madeInvisible->getUuid(), $metaData, false);
    }

    /**
     * @param UUID $uuid
     * @param Metadata $metaData
     * @param bool $madeVisible
     */
    private function updateLabels(
        UUID $uuid,
        Metadata $metaData,
        $madeVisible
    ) {
        $labelName = $this->getLabelName($metaData);

        if ($labelName) {
            $offers = $this->getRelatedOffers($uuid);

            $removeFrom = $madeVisible ? 'hiddenLabels' : 'labels';
            $addTo = $madeVisible ? 'labels' : 'hiddenLabels';

            foreach ($offers as $offer) {
                $offerLd = $offer->getBody();

                $addToArray = isset($offerLd->{$addTo}) ? $offerLd->{$addTo} : [];

                $addToArray[] = $labelName;
                $offerLd->{$addTo} = array_unique($addToArray);

                if (isset($offerLd->{$removeFrom})) {
                    $offerLd->{$removeFrom} = array_diff($offerLd->{$removeFrom}, [$labelName]);

                    if (count($offerLd->{$removeFrom}) === 0) {
                        unset($offerLd->{$removeFrom});
                    }
                }

                $this->offerRepository->save($offer->withBody($offerLd));
            }
        } else {
            $this->logger->alert('Could not apply visibility for label: ' .
                $uuid . ' because label name not found in meta data!');
        }
    }

    /**
     * @param UUID $uuid
     * @return \CultuurNet\UDB3\ReadModel\JsonDocument[]|\Generator
     */
    private function getRelatedOffers(UUID $uuid)
    {
        $offerRelations = $this->relationRepository->getOfferLabelRelations($uuid);

        foreach ($offerRelations as $offerRelation) {
            try {
                $offer = $this->offerRepository->get((string) $offerRelation->getOfferId());

                if ($offer) {
                    yield $offer;
                }
            } catch (DocumentGoneException $exception) {
                $this->logger->alert(
                    'Can not update visibility of label: "'. $offerRelation->getUuid() . '"'
                    . ' for the offer with id: "' . $offerRelation->getOfferId() . '"'
                    . ' because the document could not be retrieved.'
                );
            }
        }
    }

    /**
     * @param Metadata $metadata
     * @return string|null
     */
    private function getLabelName(Metadata $metadata)
    {
        $metadataAsArray = $metadata->serialize();

        return isset($metadataAsArray[LabelDomainMessageEnricher::LABEL_NAME]) ?
            $metadataAsArray[LabelDomainMessageEnricher::LABEL_NAME] : null;
    }
}
