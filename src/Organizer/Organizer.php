<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\UpdateableWithCdbXmlInterface;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelAwareAggregateRoot;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Organizer\Events\AddressUpdated;
use CultuurNet\UDB3\Organizer\Events\ContactPointUpdated;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerUpdatedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\TitleTranslated;
use CultuurNet\UDB3\Organizer\Events\TitleUpdated;
use CultuurNet\UDB3\Organizer\Events\WebsiteUpdated;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class Organizer extends EventSourcedAggregateRoot implements UpdateableWithCdbXmlInterface, LabelAwareAggregateRoot
{
    /**
     * The actor id.
     *
     * @var string
     */
    protected $actorId;

    /**
     * @var Language
     */
    private $mainLanguage;

    /**
     * @var Url
     */
    private $website;

    /**
     * @var Title[]
     */
    private $titles;

    /**
     * @var Address|null
     */
    private $address;

    /**
     * @var ContactPoint
     */
    private $contactPoint;

    /**
     * @var LabelCollection
     */
    private $labels;

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->actorId;
    }

    public function __construct()
    {
        // Contact points can be empty, but we only want to start recording
        // ContactPointUpdated events as soon as the organizer is updated
        // with a non-empty contact point. To enforce this we initialize the
        // aggregate state with an empty contact point.
        $this->contactPoint = new ContactPoint();
        $this->labels = new LabelCollection();
    }

    /**
     * Import from UDB2.
     *
     * @param string $actorId
     *   The actor id.
     * @param string $cdbXml
     *   The cdb xml.
     * @param string $cdbXmlNamespaceUri
     *   The cdb xml namespace uri.
     *
     * @return Organizer
     *   The actor.
     */
    public static function importFromUDB2(
        $actorId,
        $cdbXml,
        $cdbXmlNamespaceUri
    ) {
        $organizer = new static();
        $organizer->apply(
            new OrganizerImportedFromUDB2(
                $actorId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );

        return $organizer;
    }

    /**
     * Factory method to create a new Organizer.
     *
     * @param string $id
     * @param Language $mainLanguage
     * @param Url $website
     * @param Title $title
     * @return Organizer
     */
    public static function create(
        $id,
        Language $mainLanguage,
        Url $website,
        Title $title
    ) {
        $organizer = new self();

        $organizer->apply(
            new OrganizerCreatedWithUniqueWebsite($id, $mainLanguage, $website, $title)
        );

        return $organizer;
    }

    /**
     * @inheritdoc
     */
    public function updateWithCdbXml($cdbXml, $cdbXmlNamespaceUri)
    {
        $this->apply(
            new OrganizerUpdatedFromUDB2(
                $this->actorId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );
    }

    /**
     * @param Url $website
     */
    public function updateWebsite(Url $website)
    {
        if (is_null($this->website) || !$this->website->sameValueAs($website)) {
            $this->apply(
                new WebsiteUpdated(
                    $this->actorId,
                    $website
                )
            );
        }
    }

    /**
     * @param Title $title
     * @param Language $language
     */
    public function updateTitle(
        Title $title,
        Language $language
    ) {
        if ($this->isTitleChanged($title, $language)) {
            if ($language->getCode() !== $this->mainLanguage->getCode()) {
                $event = new TitleTranslated(
                    $this->actorId,
                    $title,
                    $language
                );
            } else {
                $event = new TitleUpdated(
                    $this->actorId,
                    $title
                );
            }

            $this->apply($event);
        }
    }

    /**
     * @param Address $address
     */
    public function updateAddress(Address $address)
    {
        if (is_null($this->address) || !$this->address->sameAs($address)) {
            $this->apply(
                new AddressUpdated($this->actorId, $address)
            );
        }
    }

    /**
     * @param ContactPoint $contactPoint
     */
    public function updateContactPoint(ContactPoint $contactPoint)
    {
        if (!$this->contactPoint->sameAs($contactPoint)) {
            $this->apply(
                new ContactPointUpdated($this->actorId, $contactPoint)
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function addLabel(Label $label)
    {
        if (!$this->labels->contains($label)) {
            $this->apply(new LabelAdded($this->actorId, $label));
        }
    }

    /**
     * @inheritdoc
     */
    public function removeLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->apply(new LabelRemoved($this->actorId, $label));
        }
    }

    public function delete()
    {
        $this->apply(
            new OrganizerDeleted($this->getAggregateRootId())
        );
    }

    /**
     * Apply the organizer created event.
     * @param OrganizerCreated $organizerCreated
     */
    protected function applyOrganizerCreated(OrganizerCreated $organizerCreated)
    {
        $this->actorId = $organizerCreated->getOrganizerId();

        $this->mainLanguage = new Language('nl');

        $this->setTitle($organizerCreated->getTitle(), $this->mainLanguage);
    }

    /**
     * Apply the organizer created event.
     * @param OrganizerCreatedWithUniqueWebsite $organizerCreated
     */
    protected function applyOrganizerCreatedWithUniqueWebsite(OrganizerCreatedWithUniqueWebsite $organizerCreated)
    {
        $this->actorId = $organizerCreated->getOrganizerId();

        $this->mainLanguage = $organizerCreated->getMainLanguage();

        $this->website = $organizerCreated->getWebsite();

        $this->setTitle($organizerCreated->getTitle(), $this->mainLanguage);
    }

    /**
     * @param OrganizerImportedFromUDB2 $organizerImported
     */
    protected function applyOrganizerImportedFromUDB2(
        OrganizerImportedFromUDB2 $organizerImported
    ) {
        $this->actorId = (string) $organizerImported->getActorId();

        // On import from UDB2 the default main language is 'nl'.
        $this->mainLanguage = new Language('nl');

        $actor = ActorItemFactory::createActorFromCdbXml(
            $organizerImported->getCdbXmlNamespaceUri(),
            $organizerImported->getCdbXml()
        );

        $this->setTitle($this->getTitle($actor), $this->mainLanguage);

        $this->labels = LabelCollection::fromKeywords($actor->getKeywords(true));
    }

    /**
     * @param OrganizerUpdatedFromUDB2 $organizerUpdatedFromUDB2
     */
    protected function applyOrganizerUpdatedFromUDB2(
        OrganizerUpdatedFromUDB2 $organizerUpdatedFromUDB2
    ) {
        // Note: never change the main language on update from UDB2.

        $actor = ActorItemFactory::createActorFromCdbXml(
            $organizerUpdatedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerUpdatedFromUDB2->getCdbXml()
        );

        $this->setTitle($this->getTitle($actor), $this->mainLanguage);

        $this->labels = LabelCollection::fromKeywords($actor->getKeywords(true));
    }

    /**
     * @param WebsiteUpdated $websiteUpdated
     */
    protected function applyWebsiteUpdated(WebsiteUpdated $websiteUpdated)
    {
        $this->website = $websiteUpdated->getWebsite();
    }

    /**
     * @param TitleUpdated $titleUpdated
     */
    protected function applyTitleUpdated(TitleUpdated $titleUpdated)
    {
        $this->setTitle($titleUpdated->getTitle(), $this->mainLanguage);
    }

    /**
     * @param TitleTranslated $titleTranslated
     */
    protected function applyTitleTranslated(TitleTranslated $titleTranslated)
    {
        $this->setTitle($titleTranslated->getTitle(), $titleTranslated->getLanguage());
    }

    /**
     * @param AddressUpdated $addressUpdated
     */
    protected function applyAddressUpdated(AddressUpdated $addressUpdated)
    {
        $this->address = $addressUpdated->getAddress();
    }

    /**
     * @param ContactPointUpdated $contactPointUpdated
     */
    protected function applyContactPointUpdated(ContactPointUpdated $contactPointUpdated)
    {
        $this->contactPoint = $contactPointUpdated->getContactPoint();
    }

    /**
     * @param LabelAdded $labelAdded
     */
    protected function applyLabelAdded(LabelAdded $labelAdded)
    {
        $this->labels = $this->labels->with($labelAdded->getLabel());
    }

    /**
     * @param LabelRemoved $labelRemoved
     */
    protected function applyLabelRemoved(LabelRemoved $labelRemoved)
    {
        $this->labels = $this->labels->without($labelRemoved->getLabel());
    }

    /**
     * @param \CultureFeed_Cdb_Item_Actor $actor
     * @return null|Title
     */
    private function getTitle(\CultureFeed_Cdb_Item_Actor $actor)
    {
        $details = $actor->getDetails();
        $details->rewind();

        // The first language detail found will be used to retrieve
        // properties from which in UDB3 are not any longer considered
        // to be language specific.
        if ($details->valid()) {
            return new Title($details->current()->getTitle());
        } else {
            return null;
        }
    }

    /**
     * @param Title $title
     * @param Language $language
     */
    private function setTitle(Title $title, Language $language)
    {
        $this->titles[$language->getCode()] = $title;
    }

    /**
     * @param Title $title
     * @param Language $language
     * @return bool
     */
    private function isTitleChanged(Title $title, Language $language)
    {
        return !isset($this->titles[$language->getCode()]) ||
            !$title->sameValueAs($this->titles[$language->getCode()]);
    }
}
