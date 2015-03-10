<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedLabelsMemory;

use Broadway\Serializer\SerializableInterface;

abstract class Event implements SerializableInterface
{
    /**
     * @var string
     */
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['user_id']);
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'user_id' => $this->userId,
        );
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
