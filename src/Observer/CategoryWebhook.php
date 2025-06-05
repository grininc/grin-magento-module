<?php

declare(strict_types=1);

namespace Grin\Module\Observer;

use Grin\Module\Api\PublisherInterface;
use Grin\Module\Model\Queue\StoreIdsManager;
use Grin\Module\Model\SystemConfig;
use Grin\Module\Model\WebhookStateInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Grin\Module\Api\Data\RequestInterfaceFactory;

class CategoryWebhook implements ObserverInterface
{
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var StoreIdsManager
     */
    private $storeIdsManager;

    /**
     * @var SystemConfig
     */
    private $systemConfig;

    /**
     * @param PublisherInterface $publisher
     * @param StoreIdsManager $storeIdsManager
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        PublisherInterface $publisher,
        StoreIdsManager $storeIdsManager,
        SystemConfig $systemConfig
    ) {
        $this->publisher = $publisher;
        $this->systemConfig = $systemConfig;
        $this->storeIdsManager = $storeIdsManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->systemConfig->isGrinWebhookActive()) {
            return;
        }

        $category = $observer->getDataObject();
        $storeIds = $this->storeIdsManager->filterStoreIds($category->getStoreIds());

        foreach ($storeIds as $storeId) {
            $this->publisher->publish(
                $this->buildType($category),
                [
                    'id' => (int)$category->getId(),
                    'store_id' => (int)$storeId,
                ]
            );
        }
    }

    /**
     * @param CategoryInterface $category
     * @return string
     */
    private function buildType(CategoryInterface $category): string
    {
        $prefix = $category->getEventPrefix();

        if ($category->isDeleted()) {
            return $prefix . WebhookStateInterface::POSTFIX_DELETED;
        }

        if ($category->isObjectNew()) {
            return $prefix . WebhookStateInterface::POSTFIX_CREATED;
        }

        return $prefix . WebhookStateInterface::POSTFIX_UPDATED;
    }
}
