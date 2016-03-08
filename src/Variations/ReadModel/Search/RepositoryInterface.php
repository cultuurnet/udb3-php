<?php

namespace CultuurNet\UDB3\Variations\ReadModel\Search;

use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

interface RepositoryInterface
{
    /**
     * @param Criteria $criteria
     * @param int $limit
     * @param int $page
     *
     * @return string[]
     *  A list of variation ids
     */
    public function getOfferVariations(
        Criteria $criteria,
        $limit = 30,
        $page = 0
    );

    /**
     * @param Criteria $criteria
     * @return int
     */
    public function countOfferVariations(
        Criteria $criteria
    );

    /**
     * @param Id $variationId
     * @param Url $eventUrl
     * @param OwnerId $ownerId
     * @param Purpose $purpose
     *
     * @return void
     */
    public function save(
        Id $variationId,
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose,
        OfferType $offerType
    );

    public function remove(Id $variationId);
}
