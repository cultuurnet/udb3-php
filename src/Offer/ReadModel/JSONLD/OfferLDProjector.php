<?php

namespace CultuurNet\UDB3\Offer\ReadModel\JSONLD;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Events\AbstractDescriptionTranslated;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractTitleTranslated;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\SluggerInterface;

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
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     * @param EntityServiceInterface $organizerService
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EntityServiceInterface $organizerService
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->organizerService = $organizerService;
        $this->slugger = new CulturefeedSlugger();
        $this->cdbXMLImporter = new CdbXMLImporter(
            new CdbXMLItemBaseImporter()
        );
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

        $eventLd = $document->getBody();

        $labels = isset($eventLd->labels) ? $eventLd->labels : [];
        $label = (string) $labelAdded->getLabel();

        $labels[] = $label;
        $eventLd->labels = array_unique($labels);

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * @param AbstractLabelDeleted $deleteLabel
     */
    protected function applyLabelDeleted(AbstractLabelDeleted $deleteLabel)
    {
        $document = $this->loadDocumentFromRepository($deleteLabel);

        $eventLd = $document->getBody();

        if (is_array($eventLd->labels)) {
            $eventLd->labels = array_filter(
                $eventLd->labels,
                function ($label) use ($deleteLabel) {
                    return !$deleteLabel->getLabel()->equals(
                        new Label($label)
                    );
                }
            );
            // Ensure array keys start with 0 so json_encode() does encode it
            // as an array and not as an object.
            $eventLd->labels = array_values($eventLd->labels);
        }

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * @param AbstractTitleTranslated $titleTranslated
     */
    protected function applyTitleTranslated(AbstractTitleTranslated $titleTranslated)
    {
        $document = $this->loadDocumentFromRepository($titleTranslated);

        $eventLd = $document->getBody();
        $eventLd->name->{$titleTranslated->getLanguage()->getCode(
        )} = $titleTranslated->getTitle()->toNative();

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * @param AbstractDescriptionTranslated $descriptionTranslated
     */
    protected function applyDescriptionTranslated(
        AbstractDescriptionTranslated $descriptionTranslated
    ) {
        $document = $this->loadDocumentFromRepository($descriptionTranslated);

        $eventLd = $document->getBody();
        $languageCode = $descriptionTranslated->getLanguage()->getCode();
        $description = $descriptionTranslated->getDescription()->toNative();
        if (empty($eventLd->description)) {
            $eventLd->description = new \stdClass();
        }
        $eventLd->description->{$languageCode} = $description;

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
     * @param AbstractEvent $event
     * @return JsonDocument
     */
    protected function loadDocumentFromRepository(AbstractEvent $event)
    {
        return $this->loadDocumentFromRepositoryByEventId($event->getItemId());
    }

    /**
     * @param string $eventId
     * @return JsonDocument
     */
    protected function loadDocumentFromRepositoryByEventId($eventId)
    {
        $document = $this->repository->get($eventId);

        if (!$document) {
            return $this->newDocument($eventId);
        }

        return $document;
    }
}
