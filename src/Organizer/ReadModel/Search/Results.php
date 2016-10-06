<?php

namespace CultuurNet\UDB3\Organizer\ReadModel\Search;

use ValueObjects\Number\Natural;

class Results
{

    /**
     * @var Natural
     */
    private $itemsPerPage;

    /**
     * @var array
     */
    private $member;

    /**
     * @var Natural
     */
    private $totalItems;

    /**
     * @param Natural $itemsPerPage
     * @param array $member
     * @param Natural $totalItems
     */
    public function __construct(Natural $itemsPerPage, array $member, Natural $totalItems)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->member = $member;
        $this->totalItems = $totalItems;
    }

    /**
     * @return Natural
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @return array
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @return Natural
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }
}
