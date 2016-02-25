<?php

namespace CultuurNet\UDB3\Offer\ReadModel\JSONLD;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractRemoveImage;
use CultuurNet\UDB3\Offer\Events\AbstractDescriptionTranslated;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageAdded;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageRemoved;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractTitleTranslated;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\SluggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class OfferLDProjector
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
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EntityServiceInterface $organizerService,
        SerializerInterface $mediaObjectSerializer
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->organizerService = $organizerService;
        $this->slugger = new CulturefeedSlugger();
        $this->cdbXMLImporter = new CdbXMLImporter(
            new CdbXMLItemBaseImporter()
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
    abstract protected function getTitleTranslatedClassName();

    /**
     * @return string
     */
    abstract protected function getDescriptionTranslatedClassName();

    /**
     * @param AbstractLabelAdded $labelAdded
     */
    protected function applyLabelAdded(AbstractLabelAdded $labelAdded)
    {
        $document = $this->loadDocumentFromRepository($labelAdded);

        $offerLd = $document->getBody();

        $labels = isset($offerLd->labels) ? $offerLd->labels : [];
        $label = (string) $labelAdded->getLabel();

        $labels[] = $label;
        $offerLd->labels = array_unique($labels);

        $this->repository->save($document->withBody($offerLd));
    }

    /**
     * @param AbstractLabelDeleted $deleteLabel
     */
    protected function applyLabelDeleted(AbstractLabelDeleted $deleteLabel)
    {
        $document = $this->loadDocumentFromRepository($deleteLabel);

        $offerLd = $document->getBody();

        if (is_array($offerLd->labels)) {
            $offerLd->labels = array_filter(
                $offerLd->labels,
                function ($label) use ($deleteLabel) {
                    return !$deleteLabel->getLabel()->equals(
                        new Label($label)
                    );
                }
            );
            // Ensure array keys start with 0 so json_encode() does encode it
            // as an array and not as an object.
            $offerLd->labels = array_values($offerLd->labels);
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
     * @param AbstractRemoveImage $imageRemoved
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
     * @param string $id
     * @return JsonDocument
     */
    protected function newDocument($id)
    {
        $document = new JsonDocument($id);

        $offerLd = $document->getBody();
        $offerLd->{'@id'} = $this->iriGenerator->iri($id);

        // @todo provide Event-LD context here relative to the base URI
        $offerLd->{'@context'} = '/api/1.0/event.jsonld';

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
}
