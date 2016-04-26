<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteLabel;
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

abstract class OfferCommandHandler extends Udb3CommandHandler
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
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
     * @param AbstractAddLabel $addLabel
     */
    private function handleAddLabel(AbstractAddLabel $addLabel)
    {
        $offer = $this->load($addLabel->getItemId());
        $offer->addLabel($addLabel->getLabel());
        $this->repository->save($offer);
    }

    /**
     * @param AbstractDeleteLabel $deleteLabel
     */
    private function handleDeleteLabel(AbstractDeleteLabel $deleteLabel)
    {
        $offer = $this->load($deleteLabel->getItemId());
        $offer->deleteLabel($deleteLabel->getLabel());
        $this->repository->save($offer);
    }

    /**
     * @param AbstractTranslateTitle $translateTitle
     */
    private function handleTranslateTitle(AbstractTranslateTitle $translateTitle)
    {
        $offer = $this->load($translateTitle->getItemId());
        $offer->translateTitle($translateTitle->getLanguage(), $translateTitle->getTitle());
        $this->repository->save($offer);
    }

    /**
     * @param AbstractTranslateDescription $translateDescription
     */
    private function handleTranslateDescription(AbstractTranslateDescription $translateDescription)
    {
        $offer = $this->load($translateDescription->getItemId());
        $offer->translateDescription($translateDescription->getLanguage(), $translateDescription->getDescription());
        $this->repository->save($offer);
    }

    /**
     * Handle an add image command.
     * @param AbstractAddImage $addImage
     */
    public function handleAddImage(AbstractAddImage $addImage)
    {
        $offer = $this->load($addImage->getItemId());
        $offer->addImage($addImage->getImage());
        $this->repository->save($offer);
    }

    /**
     * @param AbstractRemoveImage $removeImage
     */
    public function handleRemoveImage(AbstractRemoveImage $removeImage)
    {
        $offer = $this->load($removeImage->getItemId());
        $offer->removeImage($removeImage->getImage());
        $this->repository->save($offer);
    }

    /**
     * @param AbstractUpdateImage $updateImage
     */
    public function handleUpdateImage(AbstractUpdateImage $updateImage)
    {
        $offer = $this->load($updateImage->getItemId());
        $offer->updateImage($updateImage);
        $this->repository->save($offer);
    }

    /**
     * @param AbstractSelectMainImage $selectMainImage
     */
    public function handleSelectMainImage(AbstractSelectMainImage $selectMainImage)
    {
        $offer = $this->load($selectMainImage->getItemId());
        $offer->selectMainImage($selectMainImage->getImage());
        $this->repository->save($offer);
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

        $this->repository->save($offer);

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

        $this->repository->save($offer);

    }

    /**
     * Handle the deletion of typical age range on a place.
     * @param AbstractDeleteTypicalAgeRange $deleteTypicalAgeRange
     */
    public function handleDeleteTypicalAgeRange(AbstractDeleteTypicalAgeRange $deleteTypicalAgeRange)
    {
        $offer = $this->load($deleteTypicalAgeRange->getId());

        $offer->deleteTypicalAgeRange();

        $this->repository->save($offer);

    }

    /**
     * Handle an update command to update organizer of a place.
     * @param AbstractUpdateOrganizer $updateOrganizer
     */
    public function handleUpdateOrganizer(AbstractUpdateOrganizer $updateOrganizer)
    {
        $offer = $this->load($updateOrganizer->getId());

        $offer->updateOrganizer(
            $updateOrganizer->getOrganizerId()
        );

        $this->repository->save($offer);

    }

    /**
     * Handle an update command to delete the organizer.
     * @param AbstractDeleteOrganizer $deleteOrganizer
     */
    public function handleDeleteOrganizer(AbstractDeleteOrganizer $deleteOrganizer)
    {
        $offer = $this->load($deleteOrganizer->getId());

        $offer->deleteOrganizer(
            $deleteOrganizer->getOrganizerId()
        );

        $this->repository->save($offer);

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

        $this->repository->save($offer);

    }

    /**
     * Handle an update command to updated the booking info.
     * @param AbstractUpdateBookingInfo $updateBookingInfo
     */
    public function handleUpdateBookingInfo(AbstractUpdateBookingInfo $updateBookingInfo)
    {
        $offer = $this->load($updateBookingInfo->getId());

        $offer->updateBookingInfo(
            $updateBookingInfo->getBookingInfo()
        );

        $this->repository->save($offer);
    }

    /**
     * Makes it easier to type-hint to Offer.
     *
     * @param string $id
     * @return Offer
     */
    private function load($id)
    {
        return $this->repository->load($id);
    }
}
