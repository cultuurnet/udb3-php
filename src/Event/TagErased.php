<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Keyword;

final class TagErased extends EventEvent
{
    /**
     * @var Keyword
     */
    protected $keyword;

    public function __construct($eventId, Keyword $keyword)
    {
        parent::__construct($eventId);
        $this->keyword = $keyword;
    }

    /**
     * @return Keyword
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'keyword' => (string)$this->keyword,
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['event_id'], new Keyword($data['keyword']));
    }
}
