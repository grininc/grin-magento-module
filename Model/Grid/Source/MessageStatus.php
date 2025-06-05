<?php

declare(strict_types=1);

namespace Grin\Module\Model\Grid\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\MysqlMq\Model\QueueManagement;

class MessageStatus implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => QueueManagement::MESSAGE_STATUS_NEW,
                'label' => __('New')
            ],
            [
                'value' => QueueManagement::MESSAGE_STATUS_IN_PROGRESS,
                'label' => __('In progress')
            ],
            [
                'value' => QueueManagement::MESSAGE_STATUS_COMPLETE,
                'label' => __('Complete')
            ],
            [
                'value' => QueueManagement::MESSAGE_STATUS_RETRY_REQUIRED,
                'label' => __('Retry required')
            ],
            [
                'value' => QueueManagement::MESSAGE_STATUS_ERROR,
                'label' => __('Error')
            ],
            [
                'value' => QueueManagement::MESSAGE_STATUS_TO_BE_DELETED,
                'label' => __('To be deleted')
            ],
        ];
    }
}
