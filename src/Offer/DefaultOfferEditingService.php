<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\LabelServiceInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use ValueObjects\StringLiteral\StringLiteral;

class DefaultOfferEditingService implements OfferEditingServiceInterface
{
    /**
     * @var CommandBusInterface
     */
    protected $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var DocumentRepositoryInterface
     */
    protected $readRepository;

    /**
     * @var OfferCommandFactoryInterface
     */
    protected $commandFactory;

    /**
     * @var LabelServiceInterface
     */
    private $labelService;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $publicationDate;

    /**
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     * @param DocumentRepositoryInterface $readRepository
     * @param OfferCommandFactoryInterface $commandFactory
     * @param LabelServiceInterface $labelService
     */
    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        DocumentRepositoryInterface $readRepository,
        OfferCommandFactoryInterface $commandFactory,
        LabelServiceInterface $labelService
    ) {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->readRepository = $readRepository;
        $this->commandFactory = $commandFactory;
        $this->labelService = $labelService;
        $this->publicationDate = null;
    }

    /**
     * @param \DateTimeImmutable $publicationDate
     * @return static
     */
    public function withFixedPublicationDateForNewOffers(
        \DateTimeImmutable $publicationDate
    ) {
        $c = clone $this;
        $c->publicationDate = $publicationDate;
        return $c;
    }

    /**
     * @param $id
     * @param Label $label
     * @return string
     */
    public function addLabel($id, Label $label)
    {
        $this->guardId($id);

        $this->labelService->createLabelAggregateIfNew(
            new LabelName((string) $label),
            $label->isVisible()
        );

        return $this->commandBus->dispatch(
            $this->commandFactory->createAddLabelCommand(
                $id,
                $label
            )
        );
    }

    /**
     * @param $id
     * @param Label $label
     * @return string
     */
    public function removeLabel($id, Label $label)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createRemoveLabelCommand(
                $id,
                $label
            )
        );
    }

    /**
     * @param $id
     * @param Language $language
     * @param StringLiteral $title
     * @return string
     */
    public function translateTitle($id, Language $language, StringLiteral $title)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createTranslateTitleCommand(
                $id,
                $language,
                $title
            )
        );
    }

    /**
     * @param $id
     * @param Language $language
     * @param StringLiteral $description
     * @return string
     */
    public function translateDescription($id, Language $language, StringLiteral $description)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createTranslateDescriptionCommand(
                $id,
                $language,
                $description
            )
        );
    }

    /**
     * @param string $id
     * @param Image $image
     * @return string
     */
    public function addImage($id, Image $image)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createAddImageCommand($id, $image)
        );
    }

    /**
     * @param string $id
     * @param Image $image
     * @param StringLiteral $description
     * @param StringLiteral $copyrightHolder
     * @return string
     */
    public function updateImage(
        $id,
        Image $image,
        StringLiteral $description,
        StringLiteral $copyrightHolder
    ) {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createUpdateImageCommand(
                $id,
                $image->getMediaObjectId(),
                $description,
                $copyrightHolder
            )
        );
    }

    /**
     * @param $id
     *  Id of the offer to remove the image from.
     *
     * @param Image $image
     *  The image that should be removed.
     *
     * @return string
     */
    public function removeImage($id, Image $image)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createRemoveImageCommand($id, $image)
        );
    }

    /**
     * @param $id
     * @param Image $image
     * @return string
     */
    public function selectMainImage($id, Image $image)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createSelectMainImageCommand($id, $image)
        );
    }

    /**
     * @param string $id
     * @param string $description
     * @return string
     */
    public function updateDescription($id, $description)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createUpdateDescriptionCommand($id, $description)
        );
    }

    /**
     * @param string $id
     * @param AgeRange $ageRange
     * @return string
     */
    public function updateTypicalAgeRange($id, AgeRange $ageRange)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createUpdateTypicalAgeRangeCommand($id, $ageRange)
        );
    }

    /**
     * @param string $id
     * @return string
     */
    public function deleteTypicalAgeRange($id)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createDeleteTypicalAgeRangeCommand($id)
        );

    }

    /**
     * @param string $id
     * @param string $organizerId
     * @return string
     */
    public function updateOrganizer($id, $organizerId)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createUpdateOrganizerCommand($id, $organizerId)
        );
    }

    /**
     * @param string $id
     * @param string $organizerId
     * @return string
     */
    public function deleteOrganizer($id, $organizerId)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createDeleteOrganizerCommand($id, $organizerId)
        );
    }

    /**
     * @param string $id
     * @param ContactPoint $contactPoint
     * @return string
     */
    public function updateContactPoint($id, ContactPoint $contactPoint)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createUpdateContactPointCommand($id, $contactPoint)
        );

    }

    /**
     * @param string $id
     * @param BookingInfo $bookingInfo
     * @return string
     */
    public function updateBookingInfo($id, BookingInfo $bookingInfo)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createUpdateBookingInfoCommand($id, $bookingInfo)
        );
    }

    /**
     * @param $id
     * @param PriceInfo $priceInfo
     */
    public function updatePriceInfo($id, PriceInfo $priceInfo)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createUpdatePriceInfoCommand($id, $priceInfo)
        );
    }

    /**
     * @param string $id
     * @return string
     */
    public function delete($id)
    {
        return $this->commandBus->dispatch(
            $this->commandFactory->createDeleteOfferCommand($id)
        );
    }

    /**
     * @param string $id
     */
    public function guardId($id)
    {
        $this->readRepository->get($id);
    }
}
