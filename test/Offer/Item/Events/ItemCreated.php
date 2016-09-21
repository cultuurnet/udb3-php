<?php

namespace CultuurNet\UDB3\Offer\Item\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Offer\WorkflowStatus;

class ItemCreated implements SerializableInterface
{
    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var WorkflowStatus
     */
    protected $workflowStatus;

    /**
     * @param string $itemId
     * @param WorkflowStatus $workflowStatus
     */
    public function __construct(
        $itemId,
        WorkflowStatus $workflowStatus = null
    ) {
        $this->itemId = $itemId;
        $this->workflowStatus = $workflowStatus ? $workflowStatus : WorkflowStatus::READY_FOR_VALIDATION();
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @return WorkflowStatus
     */
    public function getWorkflowStatus()
    {
        return $this->workflowStatus;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['itemId'],
            !empty($data['workflow_status']) ? $data['workflow_status'] : null
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'itemId' => $this->itemId,
            'workflow_status' => $this->workflowStatus->toNative()
        ];
    }
}
