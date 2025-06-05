<?php

declare(strict_types=1);

namespace Grin\Module\Test\Integration\Model;

use Magento\Framework\DataObject;
use Magento\MysqlMq\Model\ResourceModel\MessageCollection;
use Magento\MysqlMq\Model\ResourceModel\MessageCollectionFactory;

class MysqlQueueMessageManager
{
    /**
     * @var MessageCollectionFactory
     */
    private $messageCollectionFactory;

    /**
     * @param MessageCollectionFactory $messageCollectionFactory
     */
    public function __construct(MessageCollectionFactory $messageCollectionFactory)
    {
        $this->messageCollectionFactory = $messageCollectionFactory;
    }

    /**
     * @return DataObject
     */
    public function getLastMessage()
    {
        /** @var MessageCollection $collection */
        $collection = $this->messageCollectionFactory->create();
        $collection->getSelect()->limit(1);
        $collection->addOrder('id');
        $collection->addFieldToFilter('topic_name', ['eq' => 'grin_module_webhook']);

        return $collection->getFirstItem();
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getLastMessages(int $limit)
    {
        /** @var MessageCollection $collection */
        $collection = $this->messageCollectionFactory->create();
        $collection->getSelect()->limit($limit);
        $collection->addOrder('id');
        $collection->addFieldToFilter('topic_name', ['eq' => 'grin_module_webhook']);

        return $collection->getItems();
    }
}
