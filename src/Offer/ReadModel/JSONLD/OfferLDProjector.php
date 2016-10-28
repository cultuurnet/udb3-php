<?php

namespace CultuurNet\UDB3\Offer\ReadModel\JSONLD;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\Cdb\CdbId\EventCdbIdExtractorInterface;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\OrganizerServiceInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Events\AbstractBookingInfoUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractContactPointUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractDescriptionTranslated;
use CultuurNet\UDB3\Offer\Events\AbstractDescriptionUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractOrganizerDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractOrganizerUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractPriceInfoUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractTypicalAgeRangeDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractTypicalAgeRangeUpdated;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageAdded;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageRemoved;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageUpdated;
use CultuurNet\UDB3\Offer\Events\Image\AbstractMainImageSelected;
use CultuurNet\UDB3\Offer\Events\AbstractTitleTranslated;
use CultuurNet\UDB3\Offer\Events\Moderation\AbstractApproved;
use CultuurNet\UDB3\Offer\Events\Moderation\AbstractFlaggedAsDuplicate;
use CultuurNet\UDB3\Offer\Events\Moderation\AbstractFlaggedAsInappropriate;
use CultuurNet\UDB3\Offer\Events\Moderation\AbstractPublished;
use CultuurNet\UDB3\Offer\Events\Moderation\AbstractRejected;
use CultuurNet\UDB3\Offer\WorkflowStatus;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\SluggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ValueObjects\Identity\UUID;

abstract class OfferLDProjector implements OrganizerServiceInterface
{
    use DelegateEventHandlingToSpecificMethodTrait {
        DelegateEventHandlingToSpecificMethodTrait::handle as handleUnknownEvents;
    }

    /**
     * @var DocumentRepositoryInterface
     */
    protected $repository;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @var EntityServiceInterface
     */
    protected $organizerService;

    /**
     * @var SluggerInterface
     */
    protected $slugger;

    /**
     * @var CdbXMLImporter
     */
    protected $cdbXMLImporter;

    /**
     * @var SerializerInterface
     */
    protected $mediaObjectSerializer;

    /**
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     * @param EntityServiceInterface $organizerService
     * @param SerializerInterface $mediaObjectSerializer
     * @param EventCdbIdExtractorInterface $eventCdbIdExtractor
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EntityServiceInterface $organizerService,
        SerializerInterface $mediaObjectSerializer,
        EventCdbIdExtractorInterface $eventCdbIdExtractor
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->organizerService = $organizerService;
        $this->slugger = new CulturefeedSlugger();

        $this->cdbXMLImporter = new CdbXMLImporter(
            new CdbXMLItemBaseImporter(),
            $eventCdbIdExtractor
        );

        $this->mediaObjectSerializer = $mediaObjectSerializer;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();

        $eventName = get_class($event);
        $eventHandlers = $this->getEventHandlers();

        if (isset($eventHandlers[$eventName])) {
            $handler = $eventHandlers[$eventName];
            call_user_func(array($this, $handler), $event);
        } else {
            $this->handleUnknownEvents($domainMessage);
        }
    }

    /**
     * @return string[]
     *   An associative array of commands and their handler methods.
     */
    private function getEventHandlers()
    {
        $events = [];

        foreach (get_class_methods($this) as $method) {
            $matches = [];

            if (preg_match('/^apply(.+)$/', $method, $matches)) {
                $event = $matches[1];
                $classNameMethod = 'get' . $event . 'ClassName';

                if (method_exists($this, $classNameMethod)) {
                    $eventFullClassName = call_user_func(array($this, $classNameMethod));
                    $events[$eventFullClassName] = $method;
                }
            }
        }

        return $events;
    }

    /**
     * @return string
     */
    abstract protected function getLabelAddedClassName();

    /**
     * @return string
     */
    abstract protected function getLabelDeletedClassName();

    /**
     * @return string
     */
    abstract protected function getImageAddedClassName();

    /**
     * @return string
     */
    abstract protected function getImageRemovedClassName();

    /**
     * @return string
     */
    abstract protected function getImageUpdatedClassName();

    /**
     * @return string
     */
    abstract protected function getMainImageSelectedClassName();

    /**
     * @return string
     */
    abstract protected function getTitleTranslatedClassName();

    /**
     * @return string
     */
    abstract protected function getDescriptionTranslatedClassName();

    /**
     * @return string
     */
    abstract protected function getOrganizerUpdatedClassName();

    /**
     * @return string
     */
    abstract protected function getOrganizerDeletedClassName();

    /**
     * @return string
     */
    abstract protected function getBookingInfoUpdatedClassName();

    /**
     * @return string
     */
    abstract protected function getPriceInfoUpdatedClassName();

    /**
     * @return string
     */
    abstract protected function getContactPointUpdatedClassName();

    /**
     * @return string
     */
    abstract protected function getDescriptionUpdatedClassName();

    /**
     * @return string
     */
    abstract protected function getTypicalAgeRangeUpdatedClassName();

    /**
     * @return string
     */
    abstract protected function getTypicalAgeRangeDeletedClassName();

    /**
     * @return string
     */
    abstract protected function getPublishedClassName();

    /**
     * @return string
     */
    abstract protected function getApprovedClassName();

    /**
     * @return string
     */
    abstract protected function getRejectedClassName();

    /**
     * @return string
     */
    abstract protected function getFlaggedAsDuplicateClassName();

    /**
     * @return string
     */
    abstract protected function getFlaggedAsInappropriateClassName();

    /**
     * @param AbstractLabelAdded $labelAdded
     */
    protected function applyLabelAdded(AbstractLabelAdded $labelAdded)
    {
        $document = $this->loadDocumentFromRepository($labelAdded);

        $offerLd = $document->getBody();

        // Check the visibility of the label to update the right property.
        $labelsProperty = $labelAdded->getLabel()->isVisible() ? 'labels' : 'hiddenLabels';

        $labels = isset($offerLd->{$labelsProperty}) ? $offerLd->{$labelsProperty} : [];
        $label = (string) $labelAdded->getLabel();

        $labels[] = $label;
        $offerLd->{$labelsProperty} = array_unique($labels);

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * @param AbstractLabelDeleted $deleteLabel
     */
    protected function applyLabelDeleted(AbstractLabelDeleted $deleteLabel)
    {
        $document = $this->loadDocumentFromRepository($deleteLabel);

        $offerLd = $document->getBody();

        // Check the visibility of the label to update the right property.
        $labelsProperty = $deleteLabel->getLabel()->isVisible() ? 'labels' : 'hiddenLabels';

        if (isset($offerLd->{$labelsProperty}) && is_array($offerLd->{$labelsProperty})) {
            $offerLd->{$labelsProperty} = array_filter(
                $offerLd->{$labelsProperty},
                function ($label) use ($deleteLabel) {
                    return !$deleteLabel->getLabel()->equals(
                        new Label($label)
                    );
                }
            );
            // Ensure array keys start with 0 so json_encode() does encode it
            // as an array and not as an object.
            if (count($offerLd->{$labelsProperty}) > 0) {
                $offerLd->{$labelsProperty} = array_values($offerLd->{$labelsProperty});
            } else {
                unset($offerLd->{$labelsProperty});
            }
        }

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * Apply the imageAdded event to the item repository.
     *
     * @param AbstractImageAdded $imageAdded
     */
    protected function applyImageAdded(AbstractImageAdded $imageAdded)
    {
        $document = $this->loadDocumentFromRepository($imageAdded);

        $offerLd = $document->getBody();
        $offerLd->mediaObject = isset($offerLd->mediaObject) ? $offerLd->mediaObject : [];

        $imageData = $this->mediaObjectSerializer
            ->serialize($imageAdded->getImage(), 'json-ld');
        $offerLd->mediaObject[] = $imageData;

        if (count($offerLd->mediaObject) === 1) {
            $offerLd->image = $imageData['contentUrl'];
        }

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * Apply the ImageUpdated event to the item repository.
     *
     * @param AbstractImageUpdated $imageUpdated
     * @throws \Exception
     */
    protected function applyImageUpdated(AbstractImageUpdated $imageUpdated)
    {
        $document = $this->loadDocumentFromRepository($imageUpdated);

        $offerLd = $document->getBody();

        if (!isset($offerLd->mediaObject)) {
            throw new \Exception('The image to update could not be found.');
        }

        $updatedMediaObjects = [];

        foreach ($offerLd->mediaObject as $mediaObject) {
            $mediaObjectMatches = (
                strpos(
                    $mediaObject->{'@id'},
                    (string)$imageUpdated->getMediaObjectId()
                ) > 0
            );

            if ($mediaObjectMatches) {
                $mediaObject->description = (string)$imageUpdated->getDescription();
                $mediaObject->copyrightHolder = (string)$imageUpdated->getCopyrightHolder();

                $updatedMediaObjects[] = $mediaObject;
            }
        };

        if (empty($updatedMediaObjects)) {
            throw new \Exception('The image to update could not be found.');
        }

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * @param AbstractImageRemoved $imageRemoved
     */
    protected function applyImageRemoved(AbstractImageRemoved $imageRemoved)
    {
        $document = $this->loadDocumentFromRepository($imageRemoved);

        $offerLd = $document->getBody();

        // Nothing to remove if there are no media objects!
        if (!isset($offerLd->mediaObject)) {
            return;
        }

        $imageId = (string) $imageRemoved->getImage()->getMediaObjectId();

        /**
         * Matches any object that is not the removed image.
         *
         * @param Object $mediaObject
         *  An existing projection of a media object.
         *
         * @return bool
         *  Returns true when the media object does not match the image to remove.
         */
        $shouldNotBeRemoved = function ($mediaObject) use ($imageId) {
            $containsId = !!strpos($mediaObject->{'@id'}, $imageId);
            return !$containsId;
        };

        // Remove any media objects that match the image.
        $filteredMediaObjects = array_filter(
            $offerLd->mediaObject,
            $shouldNotBeRemoved
        );

        // Unset the main image if it matches the removed image
        if (isset($offerLd->image) && strpos($offerLd->{'image'}, $imageId)) {
            unset($offerLd->{"image"});
        }

        if (!isset($offerLd->image) && count($filteredMediaObjects) > 0) {
            $offerLd->image = array_values($filteredMediaObjects)[0]->contentUrl;
        }

        // If no media objects are left remove the attribute.
        if (empty($filteredMediaObjects)) {
            unset($offerLd->{"mediaObject"});
        } else {
            $offerLd->mediaObject = array_values($filteredMediaObjects);
        }

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * @param AbstractMainImageSelected $mainImageSelected
     */
    protected function applyMainImageSelected(AbstractMainImageSelected $mainImageSelected)
    {
        $document = $this->loadDocumentFromRepository($mainImageSelected);
        $offerLd = $document->getBody();
        $imageId = $mainImageSelected->getImage()->getMediaObjectId();
        $mediaObjectMatcher = function ($matchingMediaObject, $currentMediaObject) use ($imageId) {
            if (!$matchingMediaObject && $this->mediaObjectMatchesId($currentMediaObject, $imageId)) {
                $matchingMediaObject = $currentMediaObject;
            }

            return $matchingMediaObject;
        };
        $mediaObject = array_reduce(
            $offerLd->mediaObject,
            $mediaObjectMatcher
        );

        $offerLd->image = $mediaObject->contentUrl;

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * @param Object $mediaObject
     * @param UUID $mediaObjectId
     *
     * @return bool
     */
    protected function mediaObjectMatchesId($mediaObject, UUID $mediaObjectId)
    {
        return strpos($mediaObject->{'@id'}, (string) $mediaObjectId) > 0;
    }

    /**
     * @param AbstractTitleTranslated $titleTranslated
     */
    protected function applyTitleTranslated(AbstractTitleTranslated $titleTranslated)
    {
        $document = $this->loadDocumentFromRepository($titleTranslated);

        $offerLd = $document->getBody();
        $offerLd->name->{$titleTranslated->getLanguage()->getCode(
        )} = $titleTranslated->getTitle()->toNative();

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * @param AbstractDescriptionTranslated $descriptionTranslated
     */
    protected function applyDescriptionTranslated(
        AbstractDescriptionTranslated $descriptionTranslated
    ) {
        $document = $this->loadDocumentFromRepository($descriptionTranslated);

        $offerLd = $document->getBody();
        $languageCode = $descriptionTranslated->getLanguage()->getCode();
        $description = $descriptionTranslated->getDescription()->toNative();
        if (empty($offerLd->description)) {
            $offerLd->description = new \stdClass();
        }
        $offerLd->description->{$languageCode} = $description;

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * Apply the organizer updated event to the offer repository.
     * @param AbstractOrganizerUpdated $organizerUpdated
     */
    protected function applyOrganizerUpdated(AbstractOrganizerUpdated $organizerUpdated)
    {
        $document = $this->loadDocumentFromRepository($organizerUpdated);

        $offerLd = $document->getBody();

        $offerLd->organizer = array(
                '@type' => 'Organizer',
            ) + (array)$this->organizerJSONLD($organizerUpdated->getOrganizerId());

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * Apply the organizer delete event to the offer repository.
     * @param AbstractOrganizerDeleted $organizerDeleted
     */
    protected function applyOrganizerDeleted(AbstractOrganizerDeleted $organizerDeleted)
    {
        $document = $this->loadDocumentFromRepository($organizerDeleted);

        $offerLd = $document->getBody();

        unset($offerLd->organizer);

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * Apply the booking info updated event to the offer repository.
     * @param AbstractBookingInfoUpdated $bookingInfoUpdated
     */
    protected function applyBookingInfoUpdated(AbstractBookingInfoUpdated $bookingInfoUpdated)
    {
        $document = $this->loadDocumentFromRepository($bookingInfoUpdated);

        $offerLd = $document->getBody();
        $offerLd->bookingInfo = $bookingInfoUpdated->getBookingInfo()->toJsonLd();

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * @param AbstractPriceInfoUpdated $priceInfoUpdated
     */
    protected function applyPriceInfoUpdated(AbstractPriceInfoUpdated $priceInfoUpdated)
    {
        $document = $this->loadDocumentFromRepository($priceInfoUpdated);

        $offerLd = $document->getBody();
        $offerLd->priceInfo = [];

        $basePrice = $priceInfoUpdated->getPriceInfo()->getBasePrice();

        $offerLd->priceInfo[] = [
            'category' => 'base',
            'name' => 'Basistarief',
            'price' => $basePrice->getPrice()->toFloat(),
            'priceCurrency' => $basePrice->getCurrency()->getCode()->toNative(),
        ];

        foreach ($priceInfoUpdated->getPriceInfo()->getTariffs() as $tariff) {
            $offerLd->priceInfo[] = [
                'category' => 'tariff',
                'name' => $tariff->getName()->toNative(),
                'price' => $tariff->getPrice()->toFloat(),
                'priceCurrency' => $tariff->getCurrency()->getCode()->toNative(),
            ];
        }

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * Apply the contact point updated event to the offer repository.
     * @param AbstractContactPointUpdated $contactPointUpdated
     */
    protected function applyContactPointUpdated(AbstractContactPointUpdated $contactPointUpdated)
    {
        $document = $this->loadDocumentFromRepository($contactPointUpdated);

        $offerLd = $document->getBody();
        $offerLd->contactPoint = $contactPointUpdated->getContactPoint()->toJsonLd();

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * Apply the description updated event to the offer repository.
     * @param AbstractDescriptionUpdated $descriptionUpdated
     */
    protected function applyDescriptionUpdated(
        AbstractDescriptionUpdated $descriptionUpdated
    ) {
        $document = $this->loadDocumentFromRepository($descriptionUpdated);

        $offerLd = $document->getBody();
        if (empty($offerLd->description)) {
            $offerLd->description = new \stdClass();
        }
        $offerLd->description->{'nl'} = $descriptionUpdated->getDescription();

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * Apply the typical age range updated event to the offer repository.
     * @param AbstractTypicalAgeRangeUpdated $typicalAgeRangeUpdated
     */
    protected function applyTypicalAgeRangeUpdated(
        AbstractTypicalAgeRangeUpdated $typicalAgeRangeUpdated
    ) {
        $document = $this->loadDocumentFromRepository($typicalAgeRangeUpdated);

        $offerLd = $document->getBody();
        $offerLd->typicalAgeRange = $typicalAgeRangeUpdated->getTypicalAgeRange();

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * Apply the typical age range deleted event to the offer repository.
     * @param AbstractTypicalAgeRangeDeleted $typicalAgeRangeDeleted
     */
    protected function applyTypicalAgeRangeDeleted(
        AbstractTypicalAgeRangeDeleted $typicalAgeRangeDeleted
    ) {
        $document = $this->loadDocumentFromRepository($typicalAgeRangeDeleted);

        $offerLd = $document->getBody();

        unset($offerLd->typicalAgeRange);

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * @param AbstractPublished $published
     */
    protected function applyPublished(AbstractPublished $published)
    {
        $this->applyEventTransformation($published, function ($offerLd) use ($published) {
            $offerLd->workflowStatus = WorkflowStatus::READY_FOR_VALIDATION()->getName();

            $publicationDate = $published->getPublicationDate();
            $offerLd->availableFrom = $publicationDate->format(\DateTime::ATOM);
        });
    }

    /**
     * @param AbstractApproved $approved
     */
    protected function applyApproved(AbstractApproved $approved)
    {
        $this->applyEventTransformation($approved, function ($offerLd) {
            $offerLd->workflowStatus = WorkflowStatus::APPROVED()->getName();
        });
    }

    /**
     * @param AbstractRejected $rejected
     */
    protected function applyRejected(AbstractRejected $rejected)
    {
        $this->applyEventTransformation($rejected, $this->reject());
    }

    /**
     * @param AbstractFlaggedAsDuplicate $flaggedAsDuplicate
     */
    protected function applyFlaggedAsDuplicate(
        AbstractFlaggedAsDuplicate $flaggedAsDuplicate
    ) {
        $this->applyEventTransformation($flaggedAsDuplicate, $this->reject());
    }

    /**
     * @param AbstractFlaggedAsInappropriate $flaggedAsInappropriate
     */
    protected function applyFlaggedAsInappropriate(
        AbstractFlaggedAsInappropriate $flaggedAsInappropriate
    ) {
        $this->applyEventTransformation($flaggedAsInappropriate, $this->reject());
    }

    /**
     * @return callable
     */
    private function reject()
    {
        return function ($offerLd) {
            $offerLd->workflowStatus = WorkflowStatus::REJECTED()->getName();
        };
    }

    /**
     * @param AbstractEvent $event
     * @param callable $transformation
     *  a transformation that you want applied to the offer-ld document
     *  the first parameter passed to the callback will be the document body
     */
    private function applyEventTransformation(AbstractEvent $event, callable $transformation)
    {
        $document = $this->loadDocumentFromRepository($event);

        $offerLd = $document->getBody();

        $transformation($offerLd);

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * @param string $id
     * @return JsonDocument
     */
    protected function newDocument($id)
    {
        $document = new JsonDocument($id);

        $offerLd = $document->getBody();
        $offerLd->{'@id'} = $this->iriGenerator->iri($id);
        $offerLd->{'@context'} = '/contexts/event';

        return $document->withBody($offerLd);
    }

    /**
     * @param AbstractEvent $event
     * @return JsonDocument
     */
    protected function loadDocumentFromRepository(AbstractEvent $event)
    {
        return $this->loadDocumentFromRepositoryByItemId($event->getItemId());
    }

    /**
     * @param string $itemId
     * @return JsonDocument
     */
    protected function loadDocumentFromRepositoryByItemId($itemId)
    {
        $document = $this->repository->get($itemId);

        if (!$document) {
            return $this->newDocument($itemId);
        }

        return $document;
    }

    /**
     * @inheritdoc
     */
    public function organizerJSONLD($organizerId)
    {
        try {
            $organizerJSONLD = $this->organizerService->getEntity(
                $organizerId
            );

            return json_decode($organizerJSONLD);
        } catch (EntityNotFoundException $e) {
            // In case the place can not be found at the moment, just add its ID
            return array(
                '@id' => $this->organizerService->iri($organizerId)
            );
        }
    }
}
