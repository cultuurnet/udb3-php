<?php

namespace CultuurNet\UDB3\Event\ReadModel\Permission;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\UiTID\UsersInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\Exception\InvalidNativeArgumentException;
use ValueObjects\String\String;
use ValueObjects\Web\EmailAddress;

class Projector implements EventListenerInterface, LoggerAwareInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;
    use LoggerAwareTrait;

    /**
     * @var UsersInterface
     */
    private $users;

    /**
     * @var PermissionRepositoryInterface
     */
    private $permissionRepository;

    public function __construct(
        UsersInterface $users,
        PermissionRepositoryInterface $permissionRepository
    ) {
        $this->users = $users;
        $this->permissionRepository = $permissionRepository;
        $this->logger = new NullLogger();
    }

    protected function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        $cdbEvent = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $createdByIdentifier = $cdbEvent->getCreatedBy();

        if ($createdByIdentifier) {
            $ownerId = $this->getUserIdByCreatedByIdentifier($createdByIdentifier);

            if (!$ownerId) {
                return;
            }

            $this->permissionRepository->markEventEditableByUser(
                new String($eventImportedFromUDB2->getEventId()),
                $ownerId
            );
        }
    }

    protected function applyEventCreatedFromCdbXml(
        EventCreatedFromCdbXml $eventCreatedFromCdbXml,
        DomainMessage $domainMessage
    ) {
        $cdbEvent = EventItemFactory::createEventFromCdbXml(
            $eventCreatedFromCdbXml->getCdbXmlNamespaceUri(),
            $eventCreatedFromCdbXml->getEventXmlString()->toEventXmlString()
        );

        $createdByIdentifier = $cdbEvent->getCreatedBy();

        // By default the owner is the user who was authenticated when creating
        // the event.
        $metadata = $domainMessage->getMetadata()->serialize();
        $ownerId = new String($metadata['user_id']);

        // If createdby is supplied, consider the user identified by createdby
        // as the owner.
        if ($createdByIdentifier) {
            $ownerId = $this->getUserIdByCreatedByIdentifier($createdByIdentifier);
        }

        if (!$ownerId) {
            return;
        }

        $this->permissionRepository->markEventEditableByUser(
            $eventCreatedFromCdbXml->getEventId(),
            $ownerId
        );
    }

    /**
     * @param $createdByIdentifier
     * @return String
     */
    private function getUserIdByCreatedByIdentifier($createdByIdentifier)
    {
        try {
            $email = new EmailAddress($createdByIdentifier);
            $userId = $this->users->byEmail($email);
        } catch (InvalidNativeArgumentException $e) {
            $nick = new String($createdByIdentifier);
            $userId = $this->users->byNick($nick);
        }

        if (!$userId) {
            $this->logger->warning(
                'Unable to find user with identifier ' . $createdByIdentifier
            );
        }

        return $userId;
    }
}
