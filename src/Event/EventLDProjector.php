<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\ReadModel\Projector;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;

class EventLDProjector extends Projector
{
    /**
     * @var DocumentRepositoryInterface
     */
    protected $repository;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     * @param Projector $placeLdProjector
     * @param Projector $organizerLdProjector
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        Projector $placeLdProjector,
        Projector $organizerLdProjector
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->placeLdProjector = $placeLdProjector;
        $this->organizerLdProjector = $organizerLdProjector;
    }

    /**
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    public function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($eventImportedFromUDB2->getEventId());
        $eventLd = $document->getBody();

        /** @var \CultureFeed_Cdb_Data_EventDetail $detail */
        $detail = null;

        /** @var \CultureFeed_Cdb_Data_EventDetail[] $details */
        $details = $udb2Event->getDetails();

        foreach ($details as $languageDetail) {
            $language = $languageDetail->getLanguage();

            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!$detail) {
                $detail = $languageDetail;
            }

            $eventLd->name[$language] = $languageDetail->getTitle();

            $descriptions = [
                $languageDetail->getShortDescription(),
                $languageDetail->getLongDescription()
            ];
            $descriptions = array_filter($descriptions);
            $eventLd->description[$language] = implode('<br/>', $descriptions);
        }

        $pictures = $detail->getMedia()->byMediaType(
            \CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO
        );

        $pictures->rewind();
        $picture = count($pictures) > 0 ? $pictures->current() : null;

        $keywords = array_filter(
            array_values($udb2Event->getKeywords()),
            function ($keyword) {
                return (strlen(trim($keyword)) > 0);
            }
        );

        $eventLd->concept = $keywords;
        $eventLd->calendarSummary = $detail->getCalendarSummary();
        $eventLd->image = $picture ? $picture->getHLink() : null;

        // Location.
        $location = array();
        $location_cdb = $udb2Event->getLocation();
        $location_id = $location_cdb->getCdbid();
        if ($location_id) {
            $location['@id'] = $this->placeLdProjector->iri($location_id);
        }
        $location['@type'] = 'Place';
        $location['name'] = $location_cdb->getLabel();
        $address = $location_cdb->getAddress()->getPhysicalAddress();
        if ($address) {
            $location['address'] = array(
                'addressCountry' => $address->getCountry(),
                'addressLocality' => $address->getCity(),
                'postalCode' => $address->getZip(),
                'streetAddress' => $address->getStreet() . ' ' . $address->getHouseNumber(),
            );
        }
        $eventLd->location = $location;

        // Organiser.
        $organiser_cdb = $udb2Event->getOrganiser();
        $contact_info_cdb = $udb2Event->getContactInfo();

        if ($organiser_cdb && $contact_info_cdb) {

            $organiser_id = $organiser_cdb->getCdbid();
            if ($organiser_id) {
                $organiser['@id'] = $this->organizerLdProjector->iri($organiser_id);
            }

            $organiser = array();
            $organiser['name'] = $organiser_cdb->getLabel();
            $organiser['email'] = array();
            $mails = $contact_info_cdb->getMails();
            foreach ($mails as $mail) {
              $organiser['email'][] = $mail->getMailAddress();
            }
            $organiser['phone'] = array();
            /** @var \CultureFeed_Cdb_Data_Phone[] $phones */
            $phones = $contact_info_cdb->getPhones();
            foreach ($phones as $phone) {
                $organiser['phone'][] = $phone->getNumber();
            }
            $eventLd->organiser = $organiser;
        }

        // Booking info.
        $bookingInfo = array(
          'description' => '',
          'name' => 'standard price',
          'price' => 0.0,
          'priceCurrency' => 'EUR',
        );
        $price = $detail->getPrice();

        if ($price) {
          $bookingInfo['description'] = $price->getDescription();
          $bookingInfo['name'] = $price->getTitle();
          $bookingInfo['price'] = floatval($price->getValue());
        }
        $eventLd->bookingInfo = $bookingInfo;


        // Input info.
        $eventLd->creator = $udb2Event->getCreatedBy();

        // format using ISO-8601 with time zone designator
        $creationDate = \DateTime::createFromFormat(
          'Y-m-d?H:i:s',
          $udb2Event->getCreationDate(),
          new \DateTimeZone('Europe/Brussels')
        );
        $eventLd->created = $creationDate->format('c');

        $eventLd->publisher = $udb2Event->getOwner();

        $eventLdModel = new JsonDocument(
            $eventImportedFromUDB2->getEventId()
        );

        $this->repository->save($eventLdModel->withBody($eventLd));
    }

    /**
     * @param EventCreated $eventCreated
     */
    protected function applyEventCreated(EventCreated $eventCreated)
    {
        // @todo This just creates an empty event. Should we do anything here?
    }

    /**
     * @param EventWasTagged $eventTagged
     */
    protected function applyEventWasTagged(EventWasTagged $eventTagged)
    {
        $document = $this->loadDocumentFromRepository($eventTagged);

        $eventLd = $document->getBody();
        // TODO: Check if the event is already tagged with the keyword?
        $eventLd->concept[] = (string)$eventTagged->getKeyword();

        $this->repository->save($document->withBody($eventLd));
    }

    public function applyTagErased(TagErased $tagErased)
    {
        $document = $this->loadDocumentFromRepository($tagErased);

        $eventLd = $document->getBody();

        $eventLd->concept = array_filter(
            $eventLd->concept,
            function ($keyword) use ($tagErased) {
                return $keyword !== (string)$tagErased->getKeyword();
            }
        );
        // Ensure array keys start with 0 so json_encode() does encode it
        // as an array and not as an object.
        $eventLd->concept = array_values($eventLd->concept);

        $this->repository->save($document->withBody($eventLd));
    }

    protected function applyTitleTranslated(TitleTranslated $titleTranslated)
    {
        $document = $this->loadDocumentFromRepository($titleTranslated);

        $eventLd = $document->getBody();
        $eventLd->name->{$titleTranslated->getLanguage()->getCode(
        )} = $titleTranslated->getTitle();

        $this->repository->save($document->withBody($eventLd));
    }

    protected function applyDescriptionTranslated(
        DescriptionTranslated $descriptionTranslated
    ) {
        $document = $this->loadDocumentFromRepository($descriptionTranslated);

        $eventLd = $document->getBody();
        $eventLd->description->{$descriptionTranslated->getLanguage()->getCode(
        )} = $descriptionTranslated->getDescription();

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * @param string $id
     * @return JsonDocument
     */
    protected function newDocument($id)
    {
        $document = new JsonDocument($id);

        $eventLd = $document->getBody();
        $eventLd->{'@id'} = $this->iriGenerator->iri($id);

        // @todo provide Event-LD context here relative to the base URI
        $eventLd->{'@context'} = '/api/1.0/event.jsonld';

        return $document->withBody($eventLd);
    }

    /**
     * @param EventEvent $event
     * @return JsonDocument
     */
    protected function loadDocumentFromRepository(EventEvent $event)
    {
        $document = $this->repository->get($event->getEventId());

        if (!$document) {
            return $this->newDocument($event->getEventId());
        }

        return $document;
    }
}
