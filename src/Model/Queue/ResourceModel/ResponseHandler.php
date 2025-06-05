<?php

declare(strict_types=1);

namespace Grin\Module\Model\Queue\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class ResponseHandler
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $data
     */
    public function addResponse(array $data)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->insert(
            $connection->getTableName('grin_queue_message_status'),
            $data
        );

        $this->deleteQueueMessagesSucceed();
        $this->deleteQueueMessagesErrors();
    }

    private function deleteQueueMessagesSucceed()
    {
        $connection = $this->resourceConnection->getConnection();
        $queueTable = $connection->getTableName('queue');
        $queueMessageStatusTable = $connection->getTableName('queue_message_status');
        $queueMessageTable = $connection->getTableName('queue_message');

        $select = $connection->select()
            ->from(['qms' => $queueMessageStatusTable], 'message_id')
            ->join(['q' => $queueTable], 'q.id = qms.queue_id', [])
            ->where('q.name = ?', 'grin_module_webhook')
            ->where('qms.updated_at <= ?', date('Y-m-d', strtotime('-30 days')))
            ->where('qms.status = 4');

        $queueIds = $connection->fetchCol($select);

        if (!empty($queueIds)) {
            $connection->delete($queueMessageTable, ['id IN (?)' => $queueIds]);
        }
    }

    private function deleteQueueMessagesErrors()
    {
        $connection = $this->resourceConnection->getConnection();
        $queueTable = $connection->getTableName('queue');
        $queueMessageStatusTable = $connection->getTableName('queue_message_status');
        $queueMessageTable = $connection->getTableName('queue_message');

        $select = $connection->select()
            ->from(['qms' => $queueMessageStatusTable], 'message_id')
            ->join(['q' => $queueTable], 'q.id = qms.queue_id', [])
            ->where('q.name = ?', 'grin_module_webhook')
            ->where('qms.updated_at <= ?', date('Y-m-d', strtotime('-30 days')))
            ->where('qms.status <> 4');

        $queueIds = $connection->fetchCol($select);

        if (!empty($queueIds)) {
            $connection->delete($queueMessageTable, ['id IN (?)' => $queueIds]);
        }
    }
}
