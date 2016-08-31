<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteOffer;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteOrganizer;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteTypicalAgeRange;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateBookingInfo;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateContactPoint;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateDescription;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateOrganizer;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateTypicalAgeRange;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractAddImage;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractRemoveImage;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractSelectMainImage;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractUpdateImage;
use CultuurNet\UDB3\Offer\Commands\AbstractTranslateDescription;
use CultuurNet\UDB3\Offer\Commands\AbstractTranslateTitle;
use CultuurNet\UDB3\Organizer\Organizer;
use ValueObjects\String\String as StringLiteral;

abstract class OfferCommandHandler extends Udb3CommandHandler
{
    /**
     * @var RepositoryInterface
     */
    protected $offerRepository;

    /**
     * @var RepositoryInterface
     */
    protected $organizerRepository;

    /**
     * @var RepositoryInterface
     */
    protected $labelRepository;

    /**
     * @param RepositoryInterface $offerRepository
     * @param RepositoryInterface $organizerRepository
     * @param ReadRepositoryInterface $labelRepository
     */
    public function __construct(
        RepositoryInterface $offerRepository,
        RepositoryInterface $organizerRepository,
        ReadRepositoryInterface $labelRepository
    ) {
        $this->offerRepository = $offerRepository;
        $this->organizerRepository = $organizerRepository;
        $this->labelRepository = $labelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($command)
    {
        $commandName = get_class($command);
        $commandHandlers = $this->getCommandHandlers();

        if (isset($commandHandlers[$commandName])) {
            $handler = $commandHandlers[$commandName];
            call_user_func(array($this, $handler), $command);
        } else {
            parent::handle($command);
        }
    }

    /**
     * @return string[]
     *   An associative array of commands and their handler methods.
     */
    private function getCommandHandlers()
    {
        $commands = [];

        foreach (get_class_methods($this) as $method) {
            $matches = [];
            if (preg_match('/^handle(.+)$/', $method, $matches)) {
                $command = $matches[1];
                $classNameMethod = 'get' . $command . 'ClassName';

                if (method_exists($this, $classNameMethod)) {
                    $commandFullClassName = call_user_func(array($this, $classNameMethod));
                    $commands[$commandFullClassName] = $method;
                }
            }
        }

        return $commands;
    }

    /**
     * @return string
     */
    abstract protected function getAddLabelClassName();

    /**
     * @return string
     */
    abstract protected function getDeleteLabelClassName();

    /**
     * @return string
     */
    abstract protected function getTranslateTitleClassName();

    /**
     * @return string
     */
    abstract protected function getTranslateDescriptionClassName();

    /**
     * @return string
     */
    abstract protected function getAddImageClassName();

    /**
     * @return string
     */
    abstract protected function getUpdateImageClassName();

    /**
     * @return string
     */
    abstract protected function getRemoveImageClassName();

    /**
     * @return string
     */
    abstract protected function getSelectMainImageClassName();

    /**
     * @return string
     */
    abstract protected function getUpdateDescriptionClassName();

    /**
     * @return string
     */
    abstract protected function getUpdateTypicalAgeRangeClassName();

    /**
     * @return string
     */
    abstract protected function getDeleteTypicalAgeRangeClassName();

    /**
     * @return string
     */
    abstract protected function getUpdateOrganizerClassName();

    /**
     * @return string
     */
    abstract protected function getDeleteOrganizerClassName();

    /**
     * @return string
     */
    abstract protected function getUpdateContactPointClassName();

    /**
     * @return string
     */
    abstract protected function getUpdateBookingInfoClassName();

    /**
     * @return string
     */
    abstract protected function getDeleteOfferClassName();

    /**
     * @param AbstractAddLabel $addLabel
     */
    private function handleAddLabel(AbstractAddLabel $addLabel)
    {
        $offer = $this->load($addLabel->getItemId());

        $labelName = new StringLiteral((string)$addLabel->getLabel());
        $label = $this->labelRepository->getByName($labelName);

        $offer->addLabel(new Label(
            $label->getName()->toNative(),
            $label->getVisibility() === Visibility::VISIBLE()
        ));

        $this->offerRepository->save($offer);
    }

    /**
     * @param AbstractDeleteLabel $deleteLabel
     */
    private function handleDeleteLabel(AbstractDeleteLabel $deleteLabel)
    {
        $offer = $this->load($deleteLabel->getItemId());
        $offer->deleteLabel($deleteLabel->getLabel());
        $this->offerRepository->save($offer);
    }

    /**
     * @param AbstractTranslateTitle $translateTitle
     */
    private function handleTranslateTitle(AbstractTranslateTitle $translateTitle)
    {
        $offer = $this->load($translateTitle->getItemId());
        $offer->translateTitle($translateTitle->getLanguage(), $translateTitle->getTitle());
        $this->offerRepository->save($offer);
    }

    /**
     * @param AbstractTranslateDescription $translateDescription
     */
    private function handleTranslateDescription(AbstractTranslateDescription $translateDescription)
    {
        $offer = $this->load($translateDescription->getItemId());
        $offer->translateDescription($translateDescription->getLanguage(), $translateDescription->getDescription());
        $this->offerRepository->save($offer);
    }

    /**
     * Handle an add image command.
     * @param AbstractAddImage $addImage
     */
    public function handleAddImage(AbstractAddImage $addImage)
    {
        $offer = $this->load($addImage->getItemId());
        $offer->addImage($addImage->getImage());
        $this->offerRepository->save($offer);
    }

    /**
     * @param AbstractRemoveImage $removeImage
     */
    public function handleRemoveImage(AbstractRemoveImage $removeImage)
    {
        $offer = $this->load($removeImage->getItemId());
        $offer->removeImage($removeImage->getImage());
        $this->offerRepository->save($offer);
    }

    /**
     * @param AbstractUpdateImage $updateImage
     */
    public function handleUpdateImage(AbstractUpdateImage $updateImage)
    {
        $offer = $this->load($updateImage->getItemId());
        $offer->updateImage($updateImage);
        $this->offerRepository->save($offer);
    }

    /**
     * @param AbstractSelectMainImage $selectMainImage
     */
    public function handleSelectMainImage(AbstractSelectMainImage $selectMainImage)
    {
        $offer = $this->load($selectMainImage->getItemId());
        $offer->selectMainImage($selectMainImage->getImage());
        $this->offerRepository->save($offer);
    }

    /**
     * Handle the update of description on a place.
     * @param AbstractUpdateDescription $updateDescription
     */
    public function handleUpdateDescription(AbstractUpdateDescription $updateDescription)
    {
        $offer = $this->load($updateDescription->getId());

        $offer->updateDescription(
            $updateDescription->getDescription()
        );

        $this->offerRepository->save($offer);

    }

    /**
     * Handle the update of typical age range on a place.
     * @param AbstractUpdateTypicalAgeRange $updateTypicalAgeRange
     */
    public function handleUpdateTypicalAgeRange(AbstractUpdateTypicalAgeRange $updateTypicalAgeRange)
    {
        $offer = $this->load($updateTypicalAgeRange->getId());

        $offer->updateTypicalAgeRange(
            $updateTypicalAgeRange->getTypicalAgeRange()
        );

        $this->offerRepository->save($offer);

    }

    /**
     * Handle the deletion of typical age range on a place.
     * @param AbstractDeleteTypicalAgeRange $deleteTypicalAgeRange
     */
    public function handleDeleteTypicalAgeRange(AbstractDeleteTypicalAgeRange $deleteTypicalAgeRange)
    {
        $offer = $this->load($deleteTypicalAgeRange->getItemId());

        $offer->deleteTypicalAgeRange();

        $this->offerRepository->save($offer);

    }

    /**
     * Handle an update command to update organizer of a place.
     * @param AbstractUpdateOrganizer $updateOrganizer
     */
    public function handleUpdateOrganizer(AbstractUpdateOrganizer $updateOrganizer)
    {
        $offer = $this->load($updateOrganizer->getItemId());
        $this->loadOrganizer($updateOrganizer->getOrganizerId());

        $offer->updateOrganizer(
            $updateOrganizer->getOrganizerId()
        );

        $this->offerRepository->save($offer);
    }

    /**
     * Handle an update command to delete the organizer.
     * @param AbstractDeleteOrganizer $deleteOrganizer
     */
    public function handleDeleteOrganizer(AbstractDeleteOrganizer $deleteOrganizer)
    {
        $offer = $this->load($deleteOrganizer->getItemId());

        $offer->deleteOrganizer(
            $deleteOrganizer->getOrganizerId()
        );

        $this->offerRepository->save($offer);
    }

    /**
     * Handle an update command to updated the contact point.
     * @param AbstractUpdateContactPoint $updateContactPoint
     */
    public function handleUpdateContactPoint(AbstractUpdateContactPoint $updateContactPoint)
    {
        $offer = $this->load($updateContactPoint->getId());

        $offer->updateContactPoint(
            $updateContactPoint->getContactPoint()
        );

        $this->offerRepository->save($offer);

    }

    /**
     * Handle an update command to updated the booking info.
     * @param AbstractUpdateBookingInfo $updateBookingInfo
     */
    public function handleUpdateBookingInfo(AbstractUpdateBookingInfo $updateBookingInfo)
    {
        $offer = $this->load($updateBookingInfo->getItemId());

        $offer->updateBookingInfo(
            $updateBookingInfo->getBookingInfo()
        );

        $this->offerRepository->save($offer);
    }

    /**
     * @param AbstractDeleteOffer $deleteOffer
     */
    private function handleDeleteOffer(AbstractDeleteOffer $deleteOffer)
    {
        $offer = $this->load($deleteOffer->getItemId());
        $offer->delete();
        $this->offerRepository->save($offer);
    }

    /**
     * Makes it easier to type-hint to Offer.
     *
     * @param string $id
     * @return Offer
     */
    private function load($id)
    {
        return $this->offerRepository->load($id);
    }

    /**
     * Makes it easier to type-hint to Organizer.
     *
     * @param string $id
     * @return Organizer
     */
    private function loadOrganizer($id)
    {
        return $this->organizerRepository->load($id);

    }
}
