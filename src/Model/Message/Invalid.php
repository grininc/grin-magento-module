<?php

declare(strict_types=1);

namespace Grin\Module\Model\Message;

use Grin\Module\Api\PublisherInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Phrase;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\MysqlMq\Model\ResourceModel\MessageStatusCollectionFactory;

class Invalid implements MessageInterface
{
    /**
     * @var MessageStatusCollectionFactory
     */
    private $collectionFactory;

    /**
     * @param MessageStatusCollectionFactory $collectionFactory
     */
    public function __construct(
        MessageStatusCollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheirtDoc
     */
    public function isDisplayed(): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->getSelect()
            ->joinLeft(
                ['queue' => $collection->getTable('queue')],
                'queue.id = main_table.queue_id',
                []
            )
            ->where('main_table.status = ?', QueueManagement::MESSAGE_STATUS_NEW)
            ->where('queue.name = ?', PublisherInterface::TOPIC);

        return (bool) $collection->getSize();
    }

    /**
     * @inheirtDoc
     */
    public function getIdentity()
    {
        // phpcs:ignore Magento2.Security.InsecureFunction
        return md5('GRIN_INVALID');
    }

    /**
     * @inheirtDoc
     */
    public function getText(): Phrase
    {
        return __(
            'The GRIN Module requires the Magento Cron to be running. Please start it now to begin processing webhooks.'
        );
    }

    /**
     * @inheirtDoc
     */
    public function getSeverity(): int
    {
        return self::SEVERITY_MAJOR;
    }
}
