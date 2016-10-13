<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations;

use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Label\LabelEventOfferTypeResolverInterface;
use CultuurNet\UDB3\Label\ReadModels\AbstractProjector;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class Projector extends AbstractProjector
{
    /**
     * @var WriteRepositoryInterface
     */
    private $writeRepository;

    /**
     * @var LabelEventOfferTypeResolverInterface
     */
    private $offerTypeResolver;

    /**
     * Projector constructor.
     * @param WriteRepositoryInterface $writeRepository
     * @param LabelEventOfferTypeResolverInterface $labelEventOfferTypeResolver
     */
    public function __construct(
        WriteRepositoryInterface $writeRepository,
        LabelEventOfferTypeResolverInterface $labelEventOfferTypeResolver
    ) {
        $this->writeRepository = $writeRepository;
        $this->offerTypeResolver = $labelEventOfferTypeResolver;

    }

    /**
     * @inheritdoc
     */
    public function applyLabelAdded(AbstractLabelAdded $labelAdded, Metadata $metadata)
    {
        $offerLabelRelation = $this->createOfferLabelRelation($labelAdded, $metadata);

        try {
            if (!is_null($offerLabelRelation)) {
                $this->writeRepository->save(
                    $offerLabelRelation->getUuid(),
                    $offerLabelRelation->getOfferType(),
                    $offerLabelRelation->getOfferId()
                );
            }
        } catch (UniqueConstraintViolationException $exception) {
            // By design to catch unique exception.
        }
    }

    /**
     * @inheritdoc
     */
    public function applyLabelDeleted(AbstractLabelDeleted $labelDeleted, Metadata $metadata)
    {
        $offerLabelRelation = $this->createOfferLabelRelation($labelDeleted, $metadata);

        if (!is_null($offerLabelRelation)) {
            $this->writeRepository->deleteByUuidAndOfferId(
                $offerLabelRelation->getUuid(),
                new StringLiteral($labelDeleted->getItemId())
            );
        }
    }

    /**
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    public function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2,
        Metadata $metadata
    ) {
        $eventId = $eventImportedFromUDB2->getEventId();

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $keywords = $udb2Event->getKeywords();
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @param Metadata $metadata
     * @return OfferLabelRelation
     */
    private function createOfferLabelRelation(
        AbstractLabelEvent $labelEvent,
        Metadata $metadata
    ) {
        $offerLabelRelation = null;

        $metadataArray = $metadata->serialize();

        $uuid = isset($metadataArray['labelUuid']) ? new UUID($metadataArray['labelUuid']) : null;
        $offerType = $this->offerTypeResolver->getOfferType($labelEvent);
        $offerId = new StringLiteral($labelEvent->getItemId());

        if (!is_null($uuid)) {
            $offerLabelRelation = new OfferLabelRelation(
                $uuid,
                $offerType,
                $offerId
            );
        }

        return $offerLabelRelation;
    }
}
