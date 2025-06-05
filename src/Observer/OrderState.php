<?php

declare(strict_types=1);

namespace Grin\Module\Observer;

use Grin\Module\Model\OrderTracker;
use Grin\Module\Model\SystemConfig;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\OrderInterface;

class OrderState implements ObserverInterface
{
    /**
     * @var OrderTracker
     */
    private $orderTracker;

    /**
     * @var SystemConfig
     */
    private $systemConfig;

    /**
     * @param OrderTracker $orderTracker
     * @param SystemConfig $systemConfig
     */
    public function __construct(OrderTracker $orderTracker, SystemConfig $systemConfig)
    {
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

        if ($order instanceof OrderInterface) {
            $this->orderTracker->setNew($order->isObjectNew());
        }
    }
}
