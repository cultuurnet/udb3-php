<?php

namespace CultuurNet\UDB3\Variations\Model\Properties;

use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\Command\ValidationException;

class DefaultUrlValidator implements UrlValidator
{
    /**
     * @var IriOfferIdentifierFactoryInterface
     */
    private $iriOfferIdentifierFactory;

    /**
     * @var EntityServiceInterface[]
     */
    private $entityServices;

    /**
     * DefaultUrlValidator constructor.
     * @param IriOfferIdentifierFactoryInterface $iriOfferIdentifierFactory
     */
    public function __construct(
        IriOfferIdentifierFactoryInterface $iriOfferIdentifierFactory
    ) {
        $this->iriOfferIdentifierFactory = $iriOfferIdentifierFactory;
    }

    /**
     * @param OfferType $offerType
     * @param EntityServiceInterface $entityService
     * @return static
     */
    public function withEntityService(OfferType $offerType, EntityServiceInterface $entityService)
    {
        $c = clone $this;
        $c->entityServices[$offerType->toNative()] = $entityService;
        return $c;
    }

    /**
     * @param Url $url
     */
    public function validateUrl(Url $url)
    {
        $identifier = $this->iriOfferIdentifierFactory->fromIri(
            \ValueObjects\Web\Url::fromNative((string) $url)
        );
        $offerType = $identifier->getType();
        $offerId = $identifier->getId();

        if (!isset($this->entityServices[$offerType->toNative()])) {
            throw new \LogicException("Found no repository for type {$offerType->toNative()}.");
        }

        try {
            $this->entityServices[$offerType->toNative()]->getEntity($offerId);
        } catch (EntityNotFoundException $e) {
            throw new ValidationException(
                ["Unable to load {$offerType}. The specified URL does not seem to point to an existing {$offerType}."]
            );
        }
    }
}
