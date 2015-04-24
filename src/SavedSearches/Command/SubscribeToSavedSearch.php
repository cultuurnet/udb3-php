<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use \CultureFeed_SavedSearches_SavedSearch as SavedSearch;

class SubscribeToSavedSearch
{
    /**
     * @var string
     */
    protected $userId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $frequency;

    /**
     * @param string $userId
     * @param string $name
     * @param string $query
     *
     * @throws \InvalidArgumentException
     *   When an invalid frequency value is given.
     */
    public function __construct($userId, $name, $query, $frequency = SavedSearch::NEVER) {
        $this->userId = $userId;
        $this->name = $name;
        $this->query = $query;
        $this->setFrequency($frequency);
    }

    /**
     * @param string $frequency
     *
     * @throws \InvalidArgumentException
     *   When an invalid frequency value is given.
     */
    private function setFrequency($frequency)
    {
        if (SavedSearch::validateFrequency($frequency)) {
            $this->frequency = $frequency;
        } else {
            throw new \InvalidArgumentException('Invalid value for frequency: ' . $frequency);
        }
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }
}
