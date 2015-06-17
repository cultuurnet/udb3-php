<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model\Properties;

use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\UDB2\EventNotFoundException;
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
     * @param string $regExpPattern
     * @param EventServiceInterface $eventService
     */
    public function __construct(
        $regExpPattern,
        EventServiceInterface $eventService
    ) {
        $this->regExpPattern = $regExpPattern;
        $this->eventService = $eventService;
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

        if (!array_key_exists('eventid', $matches)) {
            throw new \RuntimeException(
                'Regular expression pattern should capture group named "eventid"'
            );
        }

        $eventId = $matches['eventid'];

        try {
            $this->eventService->getEvent($eventId);
        } catch (EventNotFoundException $e) {
            throw new ValidationException(
                [
                    'Unable to load event. The specified URL does not seem to point to an existing event.'
                ]
            );
        }
    }
}
