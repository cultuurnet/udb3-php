<?php

namespace CultuurNet\UDB3\Place\ReadModel\Permission;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Offer\ReadModel\Permission\PermissionRepositoryInterface;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2Event;
use ValueObjects\String\String;

class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var CreatedByToUserIdResolverInterface
     */
    private $userIdResolver;

    /**
     * @var PermissionRepositoryInterface
     */
    private $permissionRepository;

    public function __construct(
        PermissionRepositoryInterface $permissionRepository,
        CreatedByToUserIdResolverInterface $createdByToUserIdResolver
    ) {
        $this->userIdResolver = $createdByToUserIdResolver;
        $this->permissionRepository = $permissionRepository;
    }

    protected function applyPlaceImportedFromUDB2(
        PlaceImportedFromUDB2 $placeImportedFromUDB2
    ) {
        $cdbActor = ActorItemFactory::createActorFromCdbXml(
            $placeImportedFromUDB2->getCdbXmlNamespaceUri(),
            $placeImportedFromUDB2->getCdbXml()
        );

        $createdByIdentifier = $cdbActor->getCreatedBy();

        if ($createdByIdentifier) {
            $ownerId = $this->userIdResolver->resolveCreatedByToUserId(
                new String($createdByIdentifier)
            );

            if (!$ownerId) {
                return;
            }

            $this->permissionRepository->markOfferEditableByUser(
                new String($placeImportedFromUDB2->getActorId()),
                $ownerId
            );
        }
    }

    protected function applyPlaceImportedFromUDB2Event(
        PlaceImportedFromUDB2Event $placeImportedFromUDB2
    ) {
        $cdbEvent = EventItemFactory::createEventFromCdbXml(
            $placeImportedFromUDB2->getCdbXmlNamespaceUri(),
            $placeImportedFromUDB2->getCdbXml()
        );

        $createdByIdentifier = $cdbEvent->getCreatedBy();

        if ($createdByIdentifier) {
            $ownerId = $this->userIdResolver->resolveCreatedByToUserId(
                new String($createdByIdentifier)
            );

            if (!$ownerId) {
                return;
            }

            $this->permissionRepository->markOfferEditableByUser(
                new String($placeImportedFromUDB2->getActorId()),
                $ownerId
            );
        }
    }

    protected function applyPlaceCreated(
        PlaceCreated $placeCreated,
        DomainMessage $domainMessage
    ) {
        $metadata = $domainMessage->getMetadata()->serialize();
        $ownerId = new String($metadata['user_id']);

        $this->permissionRepository->markOfferEditableByUser(
            new String($placeCreated->getPlaceId()),
            $ownerId
        );
    }
}
