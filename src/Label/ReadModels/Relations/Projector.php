<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations;

use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\LabelEventRelationTypeResolverInterface;
use CultuurNet\UDB3\Label\ReadModels\AbstractProjector;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\LabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\LabelEventInterface;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerUpdatedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ValueObjects\StringLiteral\StringLiteral;

class Projector extends AbstractProjector
{
    /**
     * @var WriteRepositoryInterface
     */
    private $writeRepository;

    /**
     * @var LabelEventRelationTypeResolverInterface
     */
    private $offerTypeResolver;

    /**
     * Projector constructor.
     * @param WriteRepositoryInterface $writeRepository
     * @param LabelEventRelationTypeResolverInterface $labelEventOfferTypeResolver
     */
    public function __construct(
        WriteRepositoryInterface $writeRepository,
        LabelEventRelationTypeResolverInterface $labelEventOfferTypeResolver
    ) {
        $this->writeRepository = $writeRepository;
        $this->offerTypeResolver = $labelEventOfferTypeResolver;

    }

    /**
     * @inheritdoc
     */
    public function applyLabelAdded(LabelEventInterface $labelAdded, Metadata $metadata)
    {
        $LabelRelation = $this->createLabelRelation($labelAdded);

        try {
            if (!is_null($LabelRelation)) {
                $this->writeRepository->save(
                    $LabelRelation->getLabelName(),
                    $LabelRelation->getRelationType(),
                    $LabelRelation->getRelationId()
                );
            }
        } catch (UniqueConstraintViolationException $exception) {
            // By design to catch unique exception.
        }
    }

    /**
     * @inheritdoc
     */
    public function applyLabelRemoved(LabelEventInterface $labelRemoved, Metadata $metadata)
    {
        $labelRelation = $this->createLabelRelation($labelRemoved);

        if (!is_null($labelRelation)) {
            $this->writeRepository->deleteByLabelNameAndRelationId(
                $labelRelation->getLabelName(),
                $labelRelation->getRelationId()
            );
        }
    }

    /**
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    public function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        $event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $this->updateLabelRelationFromCdbItem($event, RelationType::EVENT());
    }

    /**
     * @param PlaceImportedFromUDB2 $placeImportedFromUDB2
     */
    public function applyPlaceImportedFromUDB2(
        PlaceImportedFromUDB2 $placeImportedFromUDB2
    ) {
        $place = ActorItemFactory::createActorFromCdbXml(
            $placeImportedFromUDB2->getCdbXmlNamespaceUri(),
            $placeImportedFromUDB2->getCdbXml()
        );

        $this->updateLabelRelationFromCdbItem($place, RelationType::PLACE());
    }

    /**
     * @param OrganizerImportedFromUDB2 $organizerImportedFromUDB2
     */
    public function applyOrganizerImportedFromUDB2(
        OrganizerImportedFromUDB2 $organizerImportedFromUDB2
    ) {
        $organizer = ActorItemFactory::createActorFromCdbXml(
            $organizerImportedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerImportedFromUDB2->getCdbXml()
        );

        $this->updateLabelRelationFromCdbItem($organizer, RelationType::ORGANIZER());
    }

    /**
     * @param EventUpdatedFromUDB2 $eventUpdatedFromUDB2
     */
    public function applyEventUpdatedFromUDB2(
        EventUpdatedFromUDB2 $eventUpdatedFromUDB2
    ) {
        $event = EventItemFactory::createEventFromCdbXml(
            $eventUpdatedFromUDB2->getCdbXmlNamespaceUri(),
            $eventUpdatedFromUDB2->getCdbXml()
        );

        $this->updateLabelRelationFromCdbItem($event, RelationType::EVENT());
    }

    /**
     * @param PlaceUpdatedFromUDB2 $placeUpdatedFromUDB2
     */
    public function applyPlaceUpdatedFromUDB2(
        PlaceUpdatedFromUDB2 $placeUpdatedFromUDB2
    ) {
        $place = ActorItemFactory::createActorFromCdbXml(
            $placeUpdatedFromUDB2->getCdbXmlNamespaceUri(),
            $placeUpdatedFromUDB2->getCdbXml()
        );

        $this->updateLabelRelationFromCdbItem($place, RelationType::PLACE());
    }

    /**
     * @param OrganizerUpdatedFromUDB2 $organizerUpdatedFromUDB2
     */
    public function applyOrganizerUpdatedFromUDB2(
        OrganizerUpdatedFromUDB2 $organizerUpdatedFromUDB2
    ) {
        $organizer = ActorItemFactory::createActorFromCdbXml(
            $organizerUpdatedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerUpdatedFromUDB2->getCdbXml()
        );

        $this->updateLabelRelationFromCdbItem($organizer, RelationType::ORGANIZER());
    }

    /**
     * @param \CultureFeed_Cdb_Item_Base $cdbItem
     * @param RelationType $relationType
     */
    private function updateLabelRelationFromCdbItem(
        \CultureFeed_Cdb_Item_Base $cdbItem,
        RelationType $relationType
    ) {
        $relationId = new StringLiteral($cdbItem->getCdbId());

        $this->writeRepository->deleteByRelationId($relationId);

        $keywords = $cdbItem->getKeywords();
        $labelCollection = LabelCollection::fromStrings($keywords);

        foreach ($labelCollection->asArray() as $label) {
            $this->writeRepository->save(
                new LabelName((string) $label),
                $relationType,
                $relationId,
                true
            );
        }
    }

    /**
     * @param LabelEventInterface $labelEvent
     * @return LabelRelation
     */
    private function createLabelRelation(LabelEventInterface $labelEvent)
    {
        $labelRelation = null;

        $labelName = new LabelName((string) $labelEvent->getLabel());
        $relationType = $this->offerTypeResolver->getRelationType($labelEvent);
        $relationId = new StringLiteral($labelEvent->getItemId());

        $labelRelation = new LabelRelation(
            $labelName,
            $relationType,
            $relationId
        );

        return $labelRelation;
    }
}
