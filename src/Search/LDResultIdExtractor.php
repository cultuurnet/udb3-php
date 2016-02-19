<?php

namespace CultuurNet\UDB3\Search;

class LDResultIdExtractor implements ResultIdExtractorInterface
{
    /**
     * Array key that holds the linked id.
     */
    const ID_LINK_KEY = '@id';

    /**
     * Delimiter between the different parts in the @id property.
     */
    const ID_LINK_DELIMITER = '/';

    /**
     * @param array $result
     *   A search result.
     * @return string
     *   The id of the search result.
     */
    public function extract(array $result)
    {
        if (!isset($result[self::ID_LINK_KEY])) {
            throw new \LogicException(
                sprintf(
                    'Result has no %s key.',
                    self::ID_LINK_KEY
                )
            );
        }

        // Get the link with the id.
        $link = $result[self::ID_LINK_KEY];

        // Remove any trailing slashes to be safe.
        $link = rtrim($link, self::ID_LINK_DELIMITER);

        // Separate the id link in multiple pieces.
        $exploded = explode(self::ID_LINK_DELIMITER, $link);

        // The id is the last of all the separate pieces.
        return array_pop($exploded);
    }
}
