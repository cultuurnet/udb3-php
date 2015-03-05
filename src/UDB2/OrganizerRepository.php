<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\OrganizerRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainMessageInterface;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultureFeed_Cdb_Data_ActorDetail;
use CultureFeed_Cdb_Data_ActorDetailList;
use CultureFeed_Cdb_Data_Address;
use CultureFeed_Cdb_Data_ContactInfo;
use CultureFeed_Cdb_Data_Mail;
use CultureFeed_Cdb_Data_Phone;
use CultureFeed_Cdb_Data_Url;
use CultureFeed_Cdb_Default;
use CultureFeed_Cdb_Item_Actor;
use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Organizer;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
class OrganizerRepository extends ActorRepository
{

    use LoggerAwareTrait;
    use Udb2UtilityTrait;
    use \CultuurNet\UDB3\Udb3RepositoryTrait;

    /**
     * @var RepositoryInterface
     */
    protected $decoratee;

    /**
     * @var SearchServiceInterface
     */
    protected $search;

    /**
     * @var EntryAPIImprovedFactory
     */
    protected $entryAPIImprovedFactory;

    /**
     * @var boolean
     */
    protected $syncBack = false;

    /**
     * @var EventStreamDecoratorInterface[]
     */
    private $eventStreamDecorators = array();

     private $aggregateClass;

    public function __construct(
        RepositoryInterface $decoratee,
        SearchServiceInterface $search,
        EntryAPIImprovedFactory $entryAPIImprovedFactory,
        array $eventStreamDecorators = array()
    ) {
        $this->decoratee = $decoratee;
        $this->search = $search;
        $this->entryAPIImprovedFactory = $entryAPIImprovedFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
        $this->aggregateClass = Organizer::class;
    }

    public function syncBackOn()
    {
        $this->syncBack = true;
    }

    public function syncBackOff()
    {
        $this->syncBack = false;
    }

    /**
     * {@inheritdoc}
     */
    public function add(AggregateRoot $aggregate)
    {

        if ($this->syncBack) {
            // We can not directly act on the aggregate, as the uncommitted events will
            // be reset once we retrieve them, therefore we clone the object.
            $double = clone $aggregate;
            $domainEventStream = $double->getUncommittedEvents();
            $eventStream = $this->decorateForWrite(
                $aggregate,
                $domainEventStream
            );

            /** @var DomainMessageInterface $domainMessage */
            foreach ($eventStream as $domainMessage) {
                $domainEvent = $domainMessage->getPayload();
                switch (get_class($domainEvent)) {

                    case OrganizerCreated::class:
                        $this->applyOrganizerCreated($domainEvent, $domainMessage->getMetadata());
                        break;

                    default:
                        // Ignore any other actions
                }
            }
        }

        $this->decoratee->add($aggregate);
    }

    /**
     * Imports from UDB2.
     *
     * @param string $id
     *   The id.
     * @param string $actorXml
     *   The actor xml.
     * @param string $cdbSchemeUrl
     *
     * @return ActorImportedFromUDB2
     */
    protected function importFromUDB2($id, $actorXml, $cdbSchemeUrl)
    {
        return Organizer::importFromUDB2(
            $id,
            $actorXml,
            $cdbSchemeUrl
        );
    }

    /**
     * Listener on the organizerCreated event. Send a new organiezr also to UDB2 as actor.
     */
    public function applyOrganizerCreated(OrganizerCreated $organizerCreated, Metadata $metadata)
    {

        // Return untill we are allowed to give cdbids to actors.
        return $organizerCreated->getOrganizerId();

        $actor = new CultureFeed_Cdb_Item_Actor();
        $actor->setCdbId($organizerCreated->getOrganizerId());

        $nlDetail = new CultureFeed_Cdb_Data_ActorDetail();
        $nlDetail->setLanguage('nl');
        $nlDetail->setTitle($organizerCreated->getTitle());

        $details = new CultureFeed_Cdb_Data_ActorDetailList();
        $details->add($nlDetail);
        $actor->setDetails($details);

        // Create contact info
        $contactInfo = new CultureFeed_Cdb_Data_ContactInfo();

        $addresses = $organizerCreated->getAddresses();
        foreach ($addresses as $address) {
          $cdbAddress = new CultureFeed_Cdb_Data_Address($this->getPhysicalAddressForUdb3Address($address));
          $contactInfo->addAddress($cdbAddress);
        }

        $phones = $organizerCreated->getPhones();
        foreach ($phones as $phone) {
            $contactInfo->addPhone(new CultureFeed_Cdb_Data_Phone($phone));
        }

        $urls = $organizerCreated->getUrls();
        foreach ($urls as $url) {
            $contactInfo->addUrl(new CultureFeed_Cdb_Data_Url($url));
        }

        $emails = $organizerCreated->getEmails();
        foreach ($emails as $email) {
            $contactInfo->addMail(new CultureFeed_Cdb_Data_Mail($email));
        }
        $actor->setContactInfo($contactInfo);

        $categorieList = new \CultureFeed_Cdb_Data_CategoryList();
        $categorieList->add(new \CultureFeed_Cdb_Data_Category('actortype', '8.11.0.0.0', 'Organisator(en)'));
        $actor->setCategories($categorieList);

        $cdbXml = new CultureFeed_Cdb_Default();
        $cdbXml->addItem($actor);

        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->createActor((string)$cdbXml);

        return $organizerCreated->getOrganizerId();
    }
}
