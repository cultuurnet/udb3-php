<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 27/10/15
 * Time: 12:28
 */

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use ValueObjects\String\String;

class LabelsMerged implements SerializableInterface
{
    /**
     * @var LabelCollection
     */
    protected $labels;

    /**
     * @var String
     */
    protected $eventId;

    /**
     * @param String $eventId
     * @param LabelCollection $labels
     */
    public function __construct(String $eventId, LabelCollection $labels)
    {
        $this->labels = $labels;
        $this->eventId = $eventId;
    }

    /**
     * @return String
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return LabelCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        $labels = array_map(
            function ($item) {
                return new Label(
                    $item['text'],
                    $item['visible']
                );
            },
            $data['labels']
        );

        return new static(
            new String($data['event_id']),
            new LabelCollection($labels)
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        $labels = array_map(
            function (Label $label) {
                return [
                    'text' => (string) $label,
                    'visible' => $label->isVisible(),
                ];
            },
            $this->labels->asArray()
        );

        return [
            'event_id' => $this->eventId->toNative(),
            'labels' => $labels
        ];
    }
}
