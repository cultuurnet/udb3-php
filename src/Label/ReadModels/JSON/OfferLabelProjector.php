<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\ReadRepositoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class OfferLabelProjector implements EventListenerInterface, LoggerAwareInterface
{
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
            $this->applyMadeVisible($domainMessage->getPayload());
        } else if ($event instanceof MadeInvisible) {
            $this->applyMadeInvisible($domainMessage->getPayload());
        }
    }

    /**
     * @param MadeVisible $madeVisible
     */
    public function applyMadeVisible(MadeVisible $madeVisible)
    {
        $this->updateLabels($madeVisible->getUuid(), $madeVisible->getName(), true);
    }

    /**
     * @param MadeInvisible $madeInvisible
     */
    public function applyMadeInvisible(MadeInvisible $madeInvisible)
    {
        $this->updateLabels($madeInvisible->getUuid(), $madeInvisible->getName(), false);
    }

    /**
     * @param UUID $uuid
     * @param StringLiteral $labelName
     * @param bool $madeVisible
     */
    private function updateLabels(
        UUID $uuid,
        StringLiteral $labelName,
        $madeVisible
    ) {
        $offers = $this->getRelatedOffers($uuid);

        $removeFrom = $madeVisible ? 'hiddenLabels' : 'labels';
        $addTo = $madeVisible ? 'labels' : 'hiddenLabels';

        foreach ($offers as $offer) {
            $offerLd = $offer->getBody();

            $addToArray = isset($offerLd->{$addTo}) ? (array) $offerLd->{$addTo} : [];

            $addToArray[] = $labelName->toNative();
            $offerLd->{$addTo} = array_unique($addToArray);

            if (isset($offerLd->{$removeFrom})) {
                $offerLd->{$removeFrom} = array_diff((array) $offerLd->{$removeFrom}, [$labelName]);

                if (count($offerLd->{$removeFrom}) === 0) {
                    unset($offerLd->{$removeFrom});
                }
            }

            $this->offerRepository->save($offer->withBody($offerLd));
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
}
