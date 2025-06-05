<?php

declare(strict_types=1);

namespace Grin\Module\Observer;

use Grin\Module\Api\PublisherInterface;
use Grin\Module\Model\Queue\StoreIdsManager;
use Grin\Module\Model\SystemConfig;
use Grin\Module\Model\WebhookStateInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class StockItemWebhook implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
     * @param ProductRepositoryInterface $productRepository
     * @param PublisherInterface $publisher
     * @param StoreIdsManager $storeIdsManager
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        PublisherInterface $publisher,
        StoreIdsManager $storeIdsManager,
        SystemConfig $systemConfig
    ) {
        $this->productRepository = $productRepository;
        $this->publisher = $publisher;
        $this->storeIdsManager = $storeIdsManager;
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->systemConfig->isGrinWebhookActive()) {
            return;
        }

        $stockItem = $observer->getDataObject();
        $product = $this->productRepository->getById((int)$stockItem->getProductId());
        $storeIds = $this->storeIdsManager->filterStoreIds(
            $product->getStoreIds() ? $product->getStoreIds() : [$product->getStoreId()]
        );

        foreach ($storeIds as $storeId) {
            $this->publisher->publish(
                'stock_item' . WebhookStateInterface::POSTFIX_UPDATED,
                [
                    'product_id' => (int)$stockItem->getProductId(),
                    'id' => (int)$stockItem->getStockId(),
                    'store_id' => $storeId,
                ]
            );
        }
    }
}
