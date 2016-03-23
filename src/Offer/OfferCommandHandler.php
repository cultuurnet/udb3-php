<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteLabel;
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

    public function handleRemoveImage(AbstractRemoveImage $removeImage)
    {
        $offer = $this->load($removeImage->getItemId());
        $offer->removeImage($removeImage->getImage());
        $this->repository->save($offer);
    }

    public function handleUpdateImage(AbstractUpdateImage $updateImage)
    {
        $offer = $this->load($updateImage->getItemId());
        $offer->updateImage($updateImage);
        $this->repository->save($offer);
    }

    public function handleSelectMainImage(AbstractSelectMainImage $selectMainImage)
    {
        $offer = $this->load($selectMainImage->getItemId());
        $offer->selectMainImage($selectMainImage->getImage());
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
