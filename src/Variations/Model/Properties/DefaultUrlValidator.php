<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model\Properties;

use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\PlaceServiceInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\Variations\Command\ValidationException;

class DefaultUrlValidator implements UrlValidator
{
    /**
     * @var string
     */
    private $regExpPattern;

    /**
     * @var EventServiceInterface
     */
    private $eventService;

    /**
     * @var PlaceServiceInterface
     */
    private $placeService;

    /**
     * @param string $regExpPattern
     * @param EventServiceInterface $eventService
     * @param EntityServiceInterface $placeService
     */
    public function __construct(
        $regExpPattern,
        EventServiceInterface $eventService,
        EntityServiceInterface $placeService
    ) {
        $this->regExpPattern = $regExpPattern;
        $this->eventService = $eventService;
        $this->placeService = $placeService;
    }

    public function validateUrl(Url $url)
    {
        $match = @preg_match(
            '@^' . $this->regExpPattern . '$@',
            (string)$url,
            $matches
        );

        if (false === $match) {
            throw new \RuntimeException(
                'Problem evaluating regular expression pattern ' . $this->regExpPattern
            );
        }

        if (0 === $match) {
            throw new ValidationException(
                [
                    'The given URL can not be used. It might not be a cultural event, or no integration is provided with the system the cultural event is located at.'
                ]
            );
        }

        if (!array_key_exists('offertype', $matches)) {
            throw new \RuntimeException(
                'Regular expression pattern should capture group named "offertype"'
            );
        }

        if (!array_key_exists('offerid', $matches)) {
            throw new \RuntimeException(
                'Regular expression pattern should capture group named "offerid"'
            );
        }

        $offerType = $matches['offertype'];
        $offerId = $matches['offerid'];

        try {
            if ($offerType == 'event') {
                $this->eventService->getEvent($offerId);
            } elseif ($offerType == 'place') {
                $this->placeService->getEntity($offerId);
            }

        } catch (EventNotFoundException $e) {
            throw new ValidationException(
                [
                    "Unable to load {$offerType}. The specified URL does not seem to point to an existing {$offerType}."
                ]
            );
        } catch (EntityNotFoundException $e) {
            throw new ValidationException(
                [
                    "Unable to load {$offerType}. The specified URL does not seem to point to an existing {$offerType}."
                ]
            );
        }
    }
}
