<?php

namespace CultuurNet\UDB3\Search;

use CultuurNet\UDB3\Offer\OfferType;

class LDResultTypeExtractor
{
    /**
     * Array key that holds the type.
     */
    const TYPE_KEY = '@type';

    /**
     * {@inheritdoc}
     */
    public function extract($result)
    {
        if (!is_array($result)) {
            throw new \InvalidArgumentException('Result should be an array.');
        }

        if (!isset($result[self::TYPE_KEY])) {
            throw new \LogicException(
                sprintf(
                    'Result has no %s key.',
                    self::TYPE_KEY
                )
            );
        }

        return OfferType::fromNative($result[self::TYPE_KEY]);
    }
}
