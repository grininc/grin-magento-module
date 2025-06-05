<?php

declare(strict_types=1);

namespace Grin\Module\Observer;

use Grin\Module\Api\PublisherInterface;
use Grin\Module\Model\SystemConfig;
use Grin\Module\Model\Queue\StoreIdsManager;
use Grin\Module\Model\WebhookStateInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductWebhook implements ObserverInterface
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
     * @param
     */
    public function __construct(
        PublisherInterface $publisher,
        StoreIdsManager $storeIdsManager,
        SystemConfig $systemConfig
    ) {
        $this->publisher = $publisher;
        $this->storeIdsManager = $storeIdsManager;
        $this->systemConfig = $systemConfig;
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

        $product = $observer->getDataObject();
        $storeIds = $this->storeIdsManager->filterStoreIds(
            $product->getStoreIds() ? $product->getStoreIds() : [$product->getStoreId()]
        );

        foreach ($storeIds as $storeId) {
            $this->publisher->publish(
                $this->buildType($product),
                [
                    'id' => (int)$product->getId(),
                    'sku' => (string)$product->getSku(),
                    'store_id' => (int)$storeId,
                ]
            );
        }
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    private function buildType(ProductInterface $product): string
    {
        $prefix = $product->getEventPrefix();

        if ($product->isDeleted()) {
            return $prefix . WebhookStateInterface::POSTFIX_DELETED;
        }

        if ($product->isObjectNew()) {
            return $prefix . WebhookStateInterface::POSTFIX_CREATED;
        }

        return $prefix . WebhookStateInterface::POSTFIX_UPDATED;
    }
}
