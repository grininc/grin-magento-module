<?php

declare(strict_types=1);

namespace Grin\Module\Observer;

use Grin\Module\Api\PublisherInterface;
use Grin\Module\Model\SystemConfig;
use Grin\Module\Model\WebhookStateInterface;
use Grin\Module\Model\OrderTracker;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderWebhook implements ObserverInterface
{
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var OrderTracker
     */
    private $orderTracker;

    /**
     * @var SystemConfig
     */
    private $systemConfig;

    /**
     * @param PublisherInterface $publisher
     * @param OrderTracker $orderTracker
     * @param SystemConfig $systemConfig
     */
    public function __construct(PublisherInterface $publisher, OrderTracker $orderTracker, SystemConfig $systemConfig)
    {
        $this->publisher = $publisher;
        $this->orderTracker = $orderTracker;
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

        $order = $observer->getDataObject();
        $this->publisher->publish(
            $this->buildType($order),
            [
                'id' => (int) $order->getId(),
                'store_id' => (int) $order->getStoreId(),
            ]
        );
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    private function buildType(OrderInterface $order): string
    {
        $postfix = $this->orderTracker->isNew()
            ? WebhookStateInterface::POSTFIX_CREATED : WebhookStateInterface::POSTFIX_UPDATED;

        return $order->getEventPrefix() . $postfix;
    }
}
