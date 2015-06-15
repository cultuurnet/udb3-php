<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Hydra;

class PagedCollection implements \JsonSerializable
{
    /**
     * @var int
     */
    private $itemsPerPage;

    /**
     * @var int
     */
    private $totalItems;

    /**
     * @var array
     */
    private $members;

    /**
     * @param array $members
     * @param int $itemsPerPage
     * @param int $totalItems
     */
    public function __construct(array $members, $itemsPerPage, $totalItems)
    {
        $this->members = $members;

        $this->setItemsPerpage($itemsPerPage);
        $this->setTotalItems($totalItems);
    }

    /**
     * @param int $totalItems
     */
    private function setTotalItems($totalItems)
    {
        if (!is_int($totalItems)) {
            throw new \InvalidArgumentException(
                'totalItems should be an integer, got ' . gettype($totalItems)
            );
        }
        $this->totalItems = $totalItems;
    }

    /**
     * @param int $itemsPerPage
     */
    private function setItemsPerPage($itemsPerPage)
    {
        if (!is_int($itemsPerPage)) {
            throw new \InvalidArgumentException(
                'totalItems should be an integer, got ' . gettype($itemsPerPage)
            );
        }
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            '@context' => 'http://www.w3.org/ns/hydra/context.jsonld',
            '@type' => 'PagedCollection',
            'itemsPerPage' => $this->itemsPerPage,
            'totalItems' => $this->totalItems,
            'member' => $this->members,
        ];
    }
}
